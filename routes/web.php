<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

foreach (config('tenancy.identification.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // your actual routes
        Route::get('/', function () {
            return view('welcome');
        });

        Route::get('/login', function (Request $request) {
            return response()->json(['message' => 'It worked!!!'], 404);
        })->name('login');

        Route::get('/redirect', function (Request $request) {
            $request->session()->put('state', $state = Str::random(40));

            $query = http_build_query([
                'client_id' => '9e3f8b78-f5ab-4f81-8ecd-d6cb99db1073',
                'redirect_uri' => 'http://localhost/auth/callback',
                'response_type' => 'code',
                'scope' => '',
                'state' => $state,
                // 'prompt' => '', // "none", "consent", or "login"
            ]);

            return redirect('http://localhost/oauth/authorize?'.$query);
        });

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
                'redirect_uri' => 'http://localhost/auth/callback',
                'code' => $request->code,
            ]);

            return $response->json();
        });

    });
}
