<?php

namespace App\Events;

use App\Models\CToCMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Messages;
use App\Models\RoomChat;

class RedisChatEventCustomerToCustomer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $message;
    public $unread;
    public function __construct(CToCMessage $message,  $unread )
    {
        $this->message = $message;
        $this->unread = $unread ?? 0;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //return new PrivateChannel('channel-name');
        return ['chat'];
    }

    public function broadcastAs()
    {  
            return 'message_from_customer_to_customer';
    }
}
