<?php

namespace App\Events;

use App\Http\Controllers\Api\User\BadgesController;
use App\Models\OperationHistory;
use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RedisRealtimeOperationHistoryEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    // public $operation_history;
    public $data;

    public function __construct(OperationHistory $operation_history, Store $store)
    {

        $this->operation_history = $operation_history ?? null;
        $this->store = $store ?? null;
        $this->data = [
            "operation_history" =>    $this->operation_history,
            "store" =>   $this->store,
        ];
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
        if ($this->data != null) {
            return 'data_operation_history';
        }
    }
}
