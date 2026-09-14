<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Backs MockVideoProvider::generateSignedPlaybackUrl(). A real provider
 * (Bunny/Cloudflare/Vimeo) would never route through this controller — the
 * signed URL it returns points straight at the provider's CDN. This exists
 * so the "enrollment-gated, time-limited URL" pattern is genuinely testable
 * before a real provider is configured.
 *
 * The route this serves is protected by Laravel's `signed` middleware —
 * see routes/web.php — so an expired or tampered URL 403s before this
 * method even runs.
 */
class VideoStreamController extends Controller
{
    public function __invoke(Request $request, string $reference): Response
    {
        return response(
            "Mock video stream for reference [{$reference}].\n\n".
            "This confirms the signed-URL + enrollment-check pipeline is working — ".
            "no real video is served in mock mode. Configure a real VIDEO_PROVIDER ".
            "(see config/video.php) before launch.",
            200,
            ['Content-Type' => 'text/plain']
        );
    }
}
