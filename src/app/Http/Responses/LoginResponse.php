<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{

    /**
     * ログイン後のレスポンスを作成
     * @param $request
     * @return mixed
     */
    public function toResponse($request)
    {
        $role = auth()->user()->role;

        $redirectUrl = ($role === 1) ? '/admin/attendance/list' : '/attendance';

        return redirect()->intended($redirectUrl);
    }
}