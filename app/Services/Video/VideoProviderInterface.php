<?php

namespace App\Services\Video;

/**
 * Every video provider (Bunny Stream, Cloudflare Stream, Vimeo, ...) implements
 * this. Controllers and Actions depend ONLY on this interface — never on a
 * concrete provider class — so swapping providers in production is a one-line
 * config change (VIDEO_PROVIDER in .env), not a rewrite.
 *
 * $reference throughout is the PROVIDER'S internal video id/GUID — never a
 * public URL. Nothing in this codebase should construct a permanent public
 * video URL; playback always goes through generateSignedPlaybackUrl().
 */
interface VideoProviderInterface
{
    /** Upload a local file and return the provider's video reference. */
    public function upload(string $localFilePath, array $meta = []): string;

    public function delete(string $reference): bool;

    /** @return array{status: string, duration: ?int, thumbnail_url: ?string} */
    public function getMetadata(string $reference): array;

    /**
     * A signed, time-limited playback URL. This is the ONLY way lesson video
     * should ever be served to a browser — see VideoStreamController.
     */
    public function generateSignedPlaybackUrl(string $reference, int $ttlSeconds = 3600): string;

    public function getProcessingStatus(string $reference): string; // pending, ready, failed

    public function getThumbnailUrl(string $reference): ?string;

    public function getDuration(string $reference): ?int; // seconds
}
