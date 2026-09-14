<?php

namespace App\Services\Video;

/**
 * Development/demo provider — no real upload or transcoding happens. It
 * exists so the protected-video ARCHITECTURE (enrollment check → signed,
 * short-lived URL → controller) is real and testable even before a paid
 * streaming account exists. See VideoStreamController for where this is
 * actually consumed.
 *
 * NOT IMPLEMENTED: real video storage/transcoding/playback. Swap the
 * `video.provider` config value to a real provider class before launch —
 * see README "Video provider" section.
 */
class MockVideoProvider implements VideoProviderInterface
{
    public function upload(string $localFilePath, array $meta = []): string
    {
        return 'mock_'.md5($localFilePath.microtime());
    }

    public function delete(string $reference): bool
    {
        return true;
    }

    public function getMetadata(string $reference): array
    {
        return ['status' => 'ready', 'duration' => null, 'thumbnail_url' => null];
    }

    public function generateSignedPlaybackUrl(string $reference, int $ttlSeconds = 3600): string
    {
        // Real providers return their own signed CDN URL here. The mock
        // instead returns a signed URL into OUR OWN app, so the enrollment
        // check + signature-expiry behavior is genuinely exercised.
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'learn.video.mock-stream',
            now()->addSeconds($ttlSeconds),
            ['reference' => $reference]
        );
    }

    public function getProcessingStatus(string $reference): string
    {
        return 'ready';
    }

    public function getThumbnailUrl(string $reference): ?string
    {
        return null;
    }

    public function getDuration(string $reference): ?int
    {
        return null;
    }
}
