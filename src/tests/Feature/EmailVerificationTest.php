<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function test_send_email()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => "テスト三郎",
            'email' => "test10@example.com",
            'password' => "coachtech1010",
            'password_confirmation' => "coachtech1010",
        ]);

        $this->assertDatabaseHas('users', [
            'name' => "テスト三郎",
            'email' => "test10@example.com",
            'email_verified_at' => null,
        ]);

        $response->assertRedirect('/attendance');

        $user = User::where('email', 'test10@example.com')->first();
        $this->assertNotNull($user, 'ユーザーが取得できませんでした');

        Notification::assertSentTo([$user], VerifyEmail::class);

        $this->assertAuthenticatedAs($user);
    }

    public function test_induction_for_verify_email()
    {
        Notification::fake();

        $user = User::create([
            'name' => "テスト三郎",
            'email' => "test10@example.com",
            'password' => "coachtech1010",
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);

        $sendResponse = $this->actingAs($user)->post('/email/verification-notification');
        $sendResponse->assertRedirect();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $verifyResponse = $this->get($verificationUrl);

        $verifyResponse->assertRedirect('/attendance?verified=1');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
