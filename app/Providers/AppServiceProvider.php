<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('hashed_eloquent', function ($app, $config) {
            return new class($app['hash'], $config['model']) extends \Illuminate\Auth\EloquentUserProvider
            {
                public function retrieveByCredentials(array $credentials): ?\Illuminate\Contracts\Auth\Authenticatable
                {
                    if (! empty($credentials['email'])) {
                        return User::findByEmail($credentials['email']);
                    }

                    return parent::retrieveByCredentials($credentials);
                }
            };
        });
    }
}
