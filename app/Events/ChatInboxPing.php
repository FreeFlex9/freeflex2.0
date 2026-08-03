<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatInboxPing implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string  $channel,
        public int     $demandId,
        public string  $threadType,
        public int     $unreadCount,
        public ?string $preview,
        public ?string $senderName,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->channel)];
    }

    public function broadcastAs(): string
    {
        return 'chat.inbox.ping';
    }

    public function broadcastWith(): array
    {
        return [
            'demand_id'    => $this->demandId,
            'thread_type'  => $this->threadType,
            'unread_count' => $this->unreadCount,
            'preview'      => $this->preview,
            'sender_name'  => $this->senderName,
        ];
    }
}
