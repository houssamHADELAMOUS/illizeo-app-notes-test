<?php

namespace App\Providers;

use App\Domain\Announcement\Policies\AnnouncementPolicy;
use App\Domain\Announcement\Repositories\AnnouncementRepository;
use App\Domain\Tenant\Repositories\TenantRepository;
use App\Domain\User\Repositories\UserRepository;
use App\Models\Announcement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository(new User());
        });

        $this->app->bind(AnnouncementRepository::class, function ($app) {
            return new AnnouncementRepository(new Announcement());
        });

        $this->app->bind(TenantRepository::class, function ($app) {
            return new TenantRepository(new Tenant());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
    }
}
