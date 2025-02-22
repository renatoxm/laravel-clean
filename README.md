# Laravel Testing Grounds

- I created this repo to use it to test new implementations before adding them to my apps because it better isolates the features I am working at.
- It is organized by tags to make it easy to upgrade or downgrade to a stack version that I need.

## Stack

[![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=base-100)](https://laravel.com/docs/11.x)
[![Vite](https://img.shields.io/badge/vite-%23646CFF.svg?style=for-the-badge&logo=vite&logoColor=base-100)](https://vitejs.dev/)
[![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?style=for-the-badge&logo=docker&logoColor=base-100)](https://www.docker.com/)
[![Jetstream Badge](https://img.shields.io/badge/Jetstream-%231DA1F2.svg?style=for-the-badge&logo=laravel&logoColor=base-100)](https://jetstream.laravel.com/introduction.html)
[![InertiaJS Badge](https://img.shields.io/badge/InertiaJS-%238B5CF6.svg?style=for-the-badge&logo=vue.js&logoColor=base-100)](https://inertiajs.com/)
[![Laravel Multi Tenancy Badge](https://img.shields.io/badge/Laravel_Multi_Tenancy-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=base-100)](https://tenancyforlaravel.com/)

## Changelog

[Check the log](/CHANGELOG.md)

## Passport commands

**passport:keys** generate your passport keys (command puts the keys in the `storage/` folder):

```sh
sail artisan passport:keys
```

**passport:client** create your own clients for testing your OAuth2 functionality. When you run the client command, Passport will prompt you for more information about your client and will provide you with a client ID and secret:

```sh
sail artisan passport:client
```

[Authorization Code Grant With PKCE](https://laravel.com/docs/11.x/passport#code-grant-pkce)

```sh
# create a public client (to authenticate single page applications or native applications to access your API.)
sail artisan passport:client --public
```

[Password Grant Tokens](https://laravel.com/docs/11.x/passport#password-grant-tokens)

```sh
# Create a Password Grant Client Client (same as above with a simpler implementation, but no longer recommended by Oauth2 standards)
sail artisan passport:client --password
```

[Client Credentials Grant Tokens](https://laravel.com/docs/11.x/passport#client-credentials-grant-tokens)

The client credentials grant is suitable for machine-to-machine authentication. For example, you might use this grant in a scheduled job which is performing maintenance tasks over an API.

```sh
# Create Client Credentials Grant Tokens 
sail artisan passport:client --client
```

[Creating a Personal Access Client](https://laravel.com/docs/11.x/passport#creating-a-personal-access-client)

Sometimes, your users may want to issue access tokens to themselves without going through the typical authorization code redirect flow. Allowing users to issue tokens to themselves via your application's UI can be useful for allowing users to experiment with your API or may serve as a simpler approach to issuing access tokens in general.

Before your application can issue personal access tokens, you will need to create a personal access client.

```sh
# Create a Personal Access Client (used Before your application can issue personal access tokens)
sail artisan passport:client --personal
```

Once you have created a personal access client, you may issue tokens for a given user using the createToken method on the App\Models\User model instance. The createToken method accepts the name of the token as its first argument and an optional array of scopes as its second argument:

```php
use App\Models\User;
 
$user = User::find(1);
 
// Creating a token without scopes...
$token = $user->createToken('Token Name')->accessToken;
 
// Creating a token with scopes...
$token = $user->createToken('My Token', ['place-orders'])->accessToken;
```

**passport:purge** When tokens have been revoked or expired, you might want to purge them from the database.

```sh
# Purge revoked and expired tokens and auth codes...
sail artisan passport:purge
 
# Only purge tokens expired for more than 6 hours...
sail artisan passport:purge --hours=6
 
# Only purge revoked tokens and auth codes...
sail artisan passport:purge --revoked
 
# Only purge expired tokens and auth codes...
sail artisan passport:purge --expired
```

You may also configure a scheduled job in your application's routes/console.php file to automatically prune your tokens on a schedule:

```php
use Illuminate\Support\Facades\Schedule;
 
Schedule::command('passport:purge')->hourly();
```

### Prompt Parameter

```php
// 'prompt' => '', // "none", "consent", or "login"
```

The prompt parameter may be used to specify the authentication behavior of the Passport application.

If the prompt value is `none`, Passport will always throw an authentication error if the user is not already authenticated with the Passport application.

If the value is `consent`, Passport will always display the authorization approval screen, even if all scopes were previously granted to the consuming application.

When the value is `login`, the Passport application will always prompt the user to re-login to the application, even if they already have an existing session.

If `no prompt value` is provided, the user will be prompted for authorization only if they have not previously authorized access to the consuming application for the requested scopes.

## Related documentation

- [Laravel Passport authorization with Inertia.js](https://lxc.no/blog/laravel-passport-authorization-with-inertia-js/)
- [Laravel Passport login not redirecting properly with Jetstream Inertia](https://stackoverflow.com/questions/66571546/laravel-passport-login-not-redirecting-properly-with-jetstream-inertia)
- [Social Stream + Laravel Passport - Error: Driver \[authorize\] not supported](https://docs.socialstream.dev/guides/laravel-passport)
- [Tenancy v4 Documentation](https://tenancy-v4.pages.dev/)
- [Tenancy v4 Documentation - Passport Integration](https://tenancy-v4.pages.dev/integrations/passport/)
- [Passport and OAuth2 support for Laravel's Jetstream starter kit (Livewire)](https://github.com/headerx/laravel-jetstream-passport)
