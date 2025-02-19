<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/auth/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );

    $response = Http::asForm()->post('http://localhost/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '9e3f8b78-f5ab-4f81-8ecd-d6cb99db1073',
        'client_secret' => '1IljJWUVIg0bjnn5tCr7ifcSYWSWVfEXbCPjaV6i',
        'redirect_uri' => 'http://localhost/api/auth/callback',
        'code' => $request->code,
    ]);

    return $response->json();
});
