<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssued extends Notification
{
    use Queueable;

    public function __construct(private readonly Certificate $certificate) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your certificate is ready 🎉')
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You've earned your certificate for {$this->certificate->course_title}.")
            ->line("Certificate ID: {$this->certificate->certificate_id}")
            ->action('Download your certificate', route('verify.download', $this->certificate->certificate_id))
            ->line('Anyone can verify it at the link on your certificate.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Certificate issued',
            'body' => "Your certificate for {$this->certificate->course_title} is ready.",
            'url' => route('verify', $this->certificate->certificate_id),
        ];
    }
}
