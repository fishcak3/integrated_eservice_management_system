<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = auth()->user();

        // Check the user's role and redirect accordingly
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } 
        
        if ($user->hasRole('official')) {
            return redirect()->route('official.dashboard');
        }

        if ($user->hasRole('resident')) {
            return redirect()->route('resident.dashboard');
        }

        // Default fallback
        return redirect('/');
    }
}