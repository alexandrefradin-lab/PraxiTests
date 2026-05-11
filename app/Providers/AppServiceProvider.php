<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\Listeners\AwardXpOnAnswer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        AwardXpOnAnswer::register($this->app->make(GamificationEngine::class));
    }
}
