<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Not ShouldQueue — sent synchronously, consistent with the shared-hosting
// no-confirmed-queue-worker stance from PHASE_0_ARCHITECTURE.md §8.
class EnrollmentConfirmed extends Notification
{
    use Queueable;

    public function __construct(private readonly Enrollment $enrollment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You're enrolled — {$this->enrollment->course->title}")
            ->greeting("Welcome, {$notifiable->name}!")
            ->line("You're enrolled in {$this->enrollment->course->title}".
                ($this->enrollment->cohort ? " ({$this->enrollment->cohort->name})" : '').'.')
            ->action('Go to your dashboard', route('student.dashboard'))
            ->line('See you in class.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Enrollment confirmed',
            'body' => "You're enrolled in {$this->enrollment->course->title}.",
            'url' => route('student.dashboard'),
        ];
    }
}
