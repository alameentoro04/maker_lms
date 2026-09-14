<?php

use App\Services\Video\MockVideoProvider;

return [
    /*
     * Which VideoProviderInterface implementation to bind — see
     * App\Providers\VideoServiceProvider. Add real providers (Bunny Stream,
     * Cloudflare Stream, Vimeo) as their own classes implementing the same
     * interface, then map their config key here.
     */
    'provider' => env('VIDEO_PROVIDER', 'mock'),

    'providers' => [
        'mock' => MockVideoProvider::class,
        // 'bunny' => \App\Services\Video\BunnyStreamProvider::class, // NOT IMPLEMENTED
        // 'cloudflare' => \App\Services\Video\CloudflareStreamProvider::class, // NOT IMPLEMENTED
    ],
];
