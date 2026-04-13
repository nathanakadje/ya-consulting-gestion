<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // Redirection selon le rôle
        $redirect = match ($user->role) {
            'admin'         => '/dashboard',
            'project_manager'   => '/dashboard',
            'staff_member' => '/projects',   // Vue lecture seule
            default         => '/dashboard',
        };

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($redirect);
    }
}
