<?php

namespace App\Providers;

use App\Services\Maps\DeliveryFeeCalculator;
use App\Services\Maps\FakeMapProvider;
use App\Services\Maps\GoogleMapsProvider;
use App\Services\Maps\MapProvider;
use App\Services\Maps\StandardDeliveryFeeCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DeliveryFeeCalculator::class, StandardDeliveryFeeCalculator::class);

        $this->app->singleton(MapProvider::class, function ($app) {
            $key = config('services.google_maps.key');

            if (filled($key)) {
                return new GoogleMapsProvider($key);
            }

            return new FakeMapProvider;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
