<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Laptop;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('marketplace:expire-featured-listings', function () {
    $expiredCount = Laptop::query()
        ->where('is_featured', true)
        ->whereDate('featured_until', '<', today())
        ->update(['is_featured' => false]);

    $this->info("Removed featured status from {$expiredCount} expired listing(s).");
})->purpose('Remove featured status from expired laptop listings');

Schedule::command('marketplace:expire-featured-listings')->daily();
