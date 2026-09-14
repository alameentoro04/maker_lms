<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ContactRequest;
use App\Mail\ContactMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('Public/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Public/Contact');
    }

    public function submitContact(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Sent synchronously (not queued) per the shared-hosting deployment
        // strategy — no confirmed queue worker, so critical mail can't silently
        // sit unsent. See PHASE_0_ARCHITECTURE.md §8.
        Mail::to(config('mail.from.address'))->send(
            new ContactMessageReceived($data['name'], $data['email'], $data['subject'], $data['message'])
        );

        return back()->with('status', "Thanks — we've received your message and will reply by email soon.");
    }
}
