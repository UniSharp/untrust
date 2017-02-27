<?php

namespace UniSharp\Untrust;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class UntrustServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        Gate::before(function ($user, $ability, $arguments) {
            if ($user->isRoot()) {
                return true;
            }

            if (!count($arguments)) {
                return $user->hasPermission($ability);
            }
        });
    }
}
