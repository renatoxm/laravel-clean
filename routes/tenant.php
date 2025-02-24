<?php

declare(strict_types=1);
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware;
use App\Http\Controllers\Passport\AuthorizationController;
/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

/**
 * Tenant routes
 */
Route::middleware([
    'web',
    Middleware\InitializeTenancyByDomain::class,
    Middleware\PreventAccessFromUnwantedDomains::class,
    Middleware\ScopeSessions::class,
])->group(function () {

    //add tenant routes here
});

/* Loged in tenant*/
Route::middleware([
    'web',
    Middleware\InitializeTenancyByDomain::class,
    Middleware\PreventAccessFromUnwantedDomains::class,
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    //add tenant routes here
});

/**
 * Universal routes using session
 */
Route::middleware([
    'web',
    Middleware\InitializeTenancyByDomain::class,
    'universal',
])->group(function () {

    Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('passport.authorizations.authorize');

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    /**
     * Laravel (oauth2) Passport testing routes
     * IMPORTANT: Make sure to change the below variables before testing
     */

     /**
      * password_grant client:
      * The OAuth2 password grant allows your other first-party clients, such as a mobile application,
      * This allows you to issue access tokens securely to your first-party clients without requiring your users to go through the
      * entire OAuth2 authorization code redirect flow.
      *
      * personal_access client:
      * Sometimes, your users may want to issue access tokens to themselves without going through the typical authorization code redirect flow.
      * Allowing users to issue tokens to themselves via your application's UI can be useful for allowing users to experiment with your API
      * or may serve as a simpler approach to issuing access tokens in general.
      *
      * testing client:
      * use to test the standard oauth2 redirect flow
      */
     $vars = [
        'clients' => [
            'password_grant' => [
                'client_id' => '9e441e20-5fe4-448e-8289-c4dea4ed480f',
                'client_secret' => 'wgFsD7RHF7Nj3w6NtKbZIRjGyzRyo776SZsPM6vB',
                'client_name' => 'test@example.com',
                'client_password' => 'password',
            ],
            'personal_access' => [
                'client_id' => '9e441e20-6376-4411-bcaf-ae57c5c7e1b4',
                'client_secret' => 'E3HqTW6Aoqst9KhsPVTNbCkCgx7tpB6n4opbMf8G',
            ],
            'testing' => [
                // 'client_id' => '9e441485-c871-4cf0-bfd5-8205c4467be2',
                // 'client_secret' => 'uHD3avILfbc2xTlZtuxCT3Ud67uxvCAmu8FgCRRT',
                'client_id' => '9e441e20-6688-487d-9d04-be3027d4eeba',
                'client_secret' => 'cEUjKnshgbUh6JiXwrDMWgFsB62rRVl2hNczLAuD',
            ],
        ],
        'state' => 'xyz20304050',
        'response_type' => 'code',
        'scope' => '',
        'prompt' => 'consent', // "none", "consent", or "login"
        'redirect_uri' => url('/callback')
    ];

    Route::get('/redirect', function (Request $request) use($vars) {

        $query = http_build_query([
            'client_id' => $vars['clients']['testing']['client_id'],
            'redirect_uri' => $vars['redirect_uri'],
            'response_type' => $vars['response_type'],
            'scope' => $vars['scope'],
            'state' => $vars['state'],
            'prompt' => $vars['prompt'], // "none", "consent", or "login"
        ]);

        return redirect(url('/oauth/authorize?'.$query));
    });

    Route::get('/callback', function (Request $request) use($vars) {

        try {
            $state = $vars['state'];

            // state must match the senders
            throw_unless(
                $state !== '' && $state === $request->state,
                InvalidArgumentException::class,
                'Invalid state value.'
            );

            $data = [
                'grant_type' => 'authorization_code',
                'client_id' => $vars['clients']['testing']['client_id'],
                'client_secret' => $vars['clients']['testing']['client_secret'],
                'redirect_uri' => $vars['redirect_uri'],
                'code' => $request->code,
            ];

            return Inertia::render('Passport/Callback', [
                'data' => $data,
                'url' => url('/oauth/token'),
            ]);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }

    });

    Route::get('/password-grant', function () use($vars) {

        try {

            $data = [
                'grant_type' => 'password',
                'client_id' => $vars['clients']['password_grant']['client_id'],
                'client_secret' => $vars['clients']['password_grant']['client_secret'],
                'username' => $vars['clients']['password_grant']['client_name'],
                'password' => $vars['clients']['password_grant']['client_password'],
                'scope' => $vars['scope']
            ];

            return Inertia::render('Passport/Callback', [
                'data' => $data,
                'url' => url('/oauth/token'),
            ]);

        } catch (Exception $e) {
            return response()->json($e->getMessage(), 400);
        }

    });

    /**
     * Session Authenticated routes
     */
    Route::middleware([
        'web',
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
    });

});

/**
 * Passport Authenticated Universal Routes
 * */
Route::middleware([
    Middleware\InitializeTenancyByDomain::class,
    'universal',
    'auth:api',
])->group(function () {
    // add routes here
});
