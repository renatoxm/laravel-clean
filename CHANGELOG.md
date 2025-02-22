# CHANGELOG

## Tags

### laravel-only

- Feat: [Laravel 11](https://laravel.com/docs/11.x) installation

### laravel-passport

- Feat: [Laravel Passport](https://laravel.com/docs/11.x/passport)

### laravel-passport-tenancy

- Feat: [Tenancy v4 for Laravel](https://tenancy-v4.pages.dev/)

### l-p-t-jetstream

- Feat: [Laravel Jetstream 5](https://jetstream.laravel.com/)
- Fix: Added `app/Http/Middleware/TrustProxies.php`
- Fix: CORS related problems with Jetstream and Tenant

**CORS fix:**

```php
// bootstrap/app.php
...
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Illuminate\Http\Middleware\HandleCors::class,
        ...
    ]);
    $middleware->replace(Illuminate\Http\Middleware\TrustProxies::class, App\Http\Middleware\TrustProxies::class);
    $middleware->trustProxies(at: [
        '*',
    ]);
})

// config/tenancy.php
...
'features' => [
    // Stancl\Tenancy\Features\UserImpersonation::class,
    // Stancl\Tenancy\Features\TelescopeTags::class,
    // Stancl\Tenancy\Features\TenantConfig::class,
    Stancl\Tenancy\Features\CrossDomainRedirect::class,
    Stancl\Tenancy\Features\ViteBundler::class,
    // Stancl\Tenancy\Features\DisallowSqliteAttach::class,
],

// app/Providers/TenancyServiceProvider
...
public function boot()
{
    ...
    if (InitializeTenancyByRequestData::inGlobalStack()) {
        TenancyUrlGenerator::$prefixRouteNames = false;
    }
    ...
}

// config/fortify.php

    /*
    |--------------------------------------------------------------------------
    | Fortify Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Fortify will assign to the routes
    | that it registers with the application. If necessary, you may change
    | these middleware but typically this provided default is preferred.
    |
    */

    'middleware' => ['web', 'universal', \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class],

```

**Since Jetstream does not support Laravel Passport, a lot of implementation was necessary to make it work:**

- Refactor: Updated traits at `App\Models\User` model from `Laravel\Sanctum\HasApiTokens` to `Laravel\Passport\HasApiTokens`
- Fix: [Laravel Passport login not redirecting properly with Jetstream Inertia](https://stackoverflow.com/questions/66571546/laravel-passport-login-not-redirecting-properly-with-jetstream-inertia) - Overrided [Laravel Fortify](https://laravel.com/docs/11.x/fortify) LoginResponse in `App\Http\Responses` and registered in `App\Providers\FortifyServiceProvider::register`
- Fix: [Laravel Passport login not redirecting properly with Jetstream Inertia](https://stackoverflow.com/questions/66571546/laravel-passport-login-not-redirecting-properly-with-jetstream-inertia) - Overrided [Laravel Fortify](https://laravel.com/docs/11.x/fortify) TwoFactorLoginResponse in `App\Http\Responses` and registered in `App\Providers\FortifyServiceProvider::register`
- Fix: Overrided `Laravel\Passport\Http\Controllers\AuthorizationController` at `App\Http\Controllers\Passport::AuthorizationController` to replace original Passport's blade template with a vue Page.
- Feat: Overrided the default `oauth/authorize` route by placing a route to the new controller at `routes/tenant.php` see in **Notice** bellow
- Feat: Added `js\Pages\Passport\Authorize.vue` typescript vue page to replace original blade template from Laravel Passport
- Added `App\Providers::ApiServiceProvider` to centralize all Passport related services instead of using `AuthServiceProvider`
- Added Passport routes to `routes/tenants` folder
- Generate [Passport Clients on tenant creation](https://tenancy-v4.pages.dev/integrations/passport/#passport-encryption-keys)
- Feat: Added optional Passport variables to `.env.example`
- Feat: Added `'hash' => false,` in `config/auth.php` to both api and web guards
- Feat: Created a Callback vue page for testing Getting Authorization Tokens before the request code is returned
- Docs: Added documentation about Passport Oauth
- Feat: Created a new seeder to generate basic oauth clients for the tenant at `database/seeders/TenantOauthClientSeeder.php`
- Fix: public and private keys must be stored at `.env` generate them, copy at the respective variables and delete them after.

**Config:**

```sh
# Update Central migrations
sail artisan migrate

# generate your passport keys (command puts the keys in the `storage/` folder)
sail artisan passport:keys

# COPY both file contents to your .env and delete them both after, so they can work with tenants
PASSPORT_PRIVATE_KEY=
PASSPORT_PUBLIC_KEY=

# create a central client with (when prompted for redirect, type http://localhost/callback)
sail artisan passport:client

# optional for testing oauth on a new tenant (creates a new tenant)
sail artisan tinker #next two commands inside tinker screen

$tenant = App\Models\Tenant::create();
$tenant->createDomain('tenant1.localhost'); #or <othername>.localhost
exit

# Update Tenants migrations (if nothing new to migrate, you will get: Nothing to migrate. thats expected!)
sail artisan tenants:migrate 

# optional if you created a new tenant
sail artisan tenants:seed # creates an user with email 'test@example.com' and password: 'password'

# generate oauth clients for a tenant (string) or an array of tenants (array)
sail artisan tenants:seed --class=TenantOauthClientSeeder --tenants=<'the-tenant-id' or ['tenant-array-of-ids']>
```

**Testing Oauth:**

Before trying any of the routes, make sure to replace $vars array with your test data

```php

    // routes/tenant.php
    /**
     * Laravel (oauth2) Passport testing routes
     * IMPORTANT: Make sure to change the below variables before testing
     */
     $vars = [
        'client_id' => '',
        'client_secret' => '',
        'client_name' => '',
        'client_password' => '',
        'response_type' => 'code',
        'scope' => '',
        'prompt' => 'consent', // "none", "consent", or "login"
        'redirect_uri' => url('/oauth/callback'),
    ];
```

then you can access the routes in you browser using `http://localhost/redirect` to test `oauth/authorize`
or using a tenant domain like `http://tenant1.localhost/redirect`

**Notice:**

- When Using Passport with Laravel Social Stream, read the Passport guide to avoid `Error: Driver [authorize] not supported`: <https://docs.socialstream.dev/guides/laravel-passport>

- Add this route below in your `routes/web.php` or in your `routes/tenant.php` if using Multy Tenancy package (if using it for both central and tenants, place it inside the `universal` routes middleware).

```php
    /**
     * Passport protected routes
     * */
    Route::middleware('auth:api')->group(function () {
        Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('passport.authorizations.authorize');
    });
```

## Backlog

- Implement the UI to consume [Passport's JSON API](https://laravel.com/docs/11.x/passport#clients-json-api). Check [Passport and OAuth2 support for Laravel's Jetstream starter kit (Livewire)](https://github.com/headerx/laravel-jetstream-passport), it does it for Liveware.
