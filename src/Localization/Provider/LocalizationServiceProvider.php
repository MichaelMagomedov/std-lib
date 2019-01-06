<?php
/**
 * Created by PhpStorm.
 * User: etovladislav
 * Date: 31.08.18
 * Time: 15:42
 */

namespace Booking\Stdlib\Localization\Provider;

use Booking\Stdlib\Localization\Repository\LanguageRepository;
use Booking\Stdlib\Localization\Service\Impl\LanguageServiceImpl;
use Booking\Stdlib\Localization\Service\LanguageService;
use Illuminate\Support\ServiceProvider;


class LocalizationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LanguageService::class, function ($app) {
            return new LanguageServiceImpl(
                $app->make(LanguageRepository::class)
            );
        });
    }
}