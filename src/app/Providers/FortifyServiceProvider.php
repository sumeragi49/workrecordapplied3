<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Responses\RegisterResponses;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        //Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        //Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        //Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        //RateLimiter::for('login', function (Request $request) {
            //$throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            //return Limit::perMinute(5)->by($throttleKey);
        //});

        //RateLimiter::for('two-factor', function (Request $request) {
            //return Limit::perMinute(5)->by($request->session()->get('login.id'));
        //});

        Fortify::registerView(function () {
            return view('auth.staff.register');
        });

        $this->app->singleton(RegisterRequest::class, RegisterRequest::class);

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        Fortify::loginView(function () {
            if (request()->is('admin/*')) {
                return view('auth.admin.login');
            }
            return view('auth.staff.login');
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                if ($request->is('admin/login') && $user->role !== 1) {
                    return null;
                }

                if ($request->is('login') && $user->role !== 0) {
                    return null;
                }
                return $user;
            }
        });

        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    $role = auth()->user()->role;
                    return redirect($role === 1 ? '/admin/attendance/list' : '/attendance');
                }
            };
        });

        $this->app->singleton(LoginRequest::class, LoginRequest::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
