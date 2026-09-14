<?php

namespace App\Providers;

use App\Services\Video\VideoProviderInterface;
use Illuminate\Support\ServiceProvider;

class VideoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VideoProviderInterface::class, function () {
            $key = config('video.provider', 'mock');
            $class = config("video.providers.{$key}");

            abort_unless($class, 500, "Unknown video provider configured: {$key}");

            return $this->app->make($class);
        });
    }
}
