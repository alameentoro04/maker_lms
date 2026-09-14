<?php

namespace App\Mail;

use App\Models\PlatformSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $subject,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: PlatformSetting::get('general', 'support_email', config('mail.from.address')),
            replyTo: $this->senderEmail,
            subject: '[Contact] '.$this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
