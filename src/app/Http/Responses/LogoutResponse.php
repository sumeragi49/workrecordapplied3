<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\JsonResponse;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        $redirectUrl = $request->is('admin/*') || $request->headers->get('referer') && str_contains($request->headers->get('referer'), 'admin')
            ? '/admin/login'
            : '/login';

        return $request->wantsJson()
            ? new JsonResponse('', 204)
            : redirect($redirectUrl);
    }
}