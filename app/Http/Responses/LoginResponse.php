<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Http\Responses\LoginResponse as LoginResponseBase;

class LoginResponse extends LoginResponseBase
{
    /**
     * Create an HTTP response that represents the object.
     * This override fixes Laravel Passport login not redirecting properly with Jetstream Inertia
     * https://stackoverflow.com/questions/66571546/laravel-passport-login-not-redirecting-properly-with-jetstream-inertia
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // \Log::channel('oauth')->debug($request);
        // \Log::channel('oauth')->debug($request->session()->all());
        if ($request->session()->has('url.intended')) {
            // \Log::channel('oauth')->debug('inertia redirection ----------');
            return Inertia::location(session('url.intended'));
        }

        return parent::toResponse($request);
    }
}
