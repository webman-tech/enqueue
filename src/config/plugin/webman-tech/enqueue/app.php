<?php

use Illuminate\Events\Dispatcher;
use WebmanTech\Enqueue\Manager\EnqueueManager;

return [
    'enable' => true,
    'enqueue_manager' => function (): EnqueueManager {
        return new EnqueueManager(new Dispatcher());
    }
];
