<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\Passport\Client;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware as TenancyMiddleware;

class PassportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Passport::ignoreRoutes(); // they will be registred below
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /**
         * https://laravel.com/docs/11.x/passport#password-grant-tokens
         * This allows you to issue access tokens securely to your first-party
         * clients without requiring your users to go through the entire OAuth2 authorization code redirect flow.
         */
        Passport::enablePasswordGrant();

        Passport::loadKeysFrom(storage_path()); // to enable tenants to access the keys

        Passport::useClientModel(Client::class);

        if (config('passport.token_lifetimes.use_custom_lifetimes')) {
            Passport::tokensExpireIn(now()->addDays(config('passport.token_lifetimes.token_expires_in')));
            Passport::refreshTokensExpireIn(now()->addDays(config('passport.token_lifetimes.refresh_token_expires_in')));
            Passport::personalAccessTokensExpireIn(now()->addMonths(config('passport.token_lifetimes.personal_access_token_expires_in')));
        }

        Passport::tokensCan([
            'create' => 'Create resources',
            'read' => 'Read Resources',
            'update' => 'Update Resources',
            'delete' => 'Delete Resources',
        ]);

        // default scope for passport tokens
        Passport::setDefaultScope([
            // 'create',
            'read',
            // 'update',
            // 'delete',
        ]);

        Route::group([
            'as' => 'passport.',
            'middleware' => [
                'universal',
                TenancyMiddleware\InitializeTenancyByDomain::class, // Use tenancy initialization middleware of your choice
            ],
            'prefix' => config('passport.path', 'oauth'),
            'namespace' => 'Laravel\Passport\Http\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . "/../../vendor/laravel/passport/src/../routes/web.php");
        });
    }
}
