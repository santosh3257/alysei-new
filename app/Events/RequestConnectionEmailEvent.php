<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequestConnectionEmailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $connection_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($connection_id)
    {
        $this->connection_id = $connection_id;
    }

}
