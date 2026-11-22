<?php

namespace App\Providers;

use App\Models\Laptop;
use App\Models\Order;
use App\Models\Review;
use App\Models\Complaint;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\ComplaintPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Laptop::class, OrderPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(User::class, ComplaintPolicy::class);
        Gate::define('create-review', fn (User $user, Order $order) =>
            (new ReviewPolicy())->create($user, $order));
    }
}
