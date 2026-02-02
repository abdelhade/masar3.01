<?php

declare(strict_types=1);

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public ?string $icon = 'las la-bell',
        public string $type = 'info', // info, success, warning, danger
        public array $extraData = []
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return array_merge([
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
            'type' => $this->type,
            // 'created_at' is automatically handled by the database driver in the `created_at` column,
            // but we can add it here if the frontend expects it in the data payload.
            'timestamp' => now()->toIso8601String(),
        ], $this->extraData);
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'notification' => $this->toArray($notifiable),
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
     */
    public function broadcastAs(): string
    {
        return 'general-notification';
    }
}
