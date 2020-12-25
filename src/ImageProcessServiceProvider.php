<?php

namespace Jncinet\ImageProcess;

use Illuminate\Support\ServiceProvider;

class ImageProcessServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ImageProcess::class, function () {
            return new ImageProcess();
        });

        $this->app->alias(ImageProcess::class, 'image-process');
    }
}