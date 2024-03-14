<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

class PublishUserCreated implements ShouldQueue
{
    private $channel = 'user.created';

    public function handle(UserCreated $event): void
    {
        Redis::publish($this->channel, json_encode([
            'uuid' => $event->user->uuid,
            'account_uuid' => $event->user->account->uuid,
        ]));
    }
}
