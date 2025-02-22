<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Http\Responses\TwoFactorLoginResponse as TwoFactorLoginResponseBase;

class TwoFactorLoginResponse extends TwoFactorLoginResponseBase
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
        if ($request->session()->has('url.intended')) {
            return Inertia::location(session('url.intended'));
        }

        return parent::toResponse($request);
    }
}
