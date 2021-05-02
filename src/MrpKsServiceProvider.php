<?php

namespace Engazan\MrpKs;

use Illuminate\Support\ServiceProvider;

/**
 * Class MrpKsServiceProvider
 * @package Engazan\MrpKs
 * @author Engazan <Engazan.eu@icloud.com>
 */
class MrpKsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/../config/mrp-ks.php' => config_path('mrp-ks.php')
            ]
        );
    }

    public function register()
    {
        $this->app->singleton(
            MrpKs::class,
            function () {
                return new MrpKs();
            }
        );
    }
}
