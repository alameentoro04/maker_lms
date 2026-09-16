<?php

namespace App\Notifications;

use App\Models\LiveClass;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LiveClassScheduled extends Notification
{
    use Queueable;

    public function __construct(private readonly LiveClass $liveClass) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New live class: {$this->liveClass->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A new live class has been scheduled: {$this->liveClass->title}")
            ->line('When: '.$this->liveClass->starts_at->format('l, d M Y \a\t g:ia'))
            ->action('View details', route('learn.show', $this->liveClass->cohort->course))
            ->line('The join link will be available on the class page.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New live class scheduled',
            'body' => "{$this->liveClass->title} — ".$this->liveClass->starts_at->format('d M, g:ia'),
            'url' => route('learn.show', $this->liveClass->cohort->course),
        ];
    }
}
