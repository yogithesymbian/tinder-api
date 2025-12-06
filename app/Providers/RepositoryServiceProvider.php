<?php

namespace App\Providers;

use App\Repositories\Contracts\PeopleRepositoryInterface;
use App\Repositories\Eloquent\PeopleRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PeopleRepositoryInterface::class, PeopleRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
