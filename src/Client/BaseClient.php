<?php

namespace WebmanTech\Enqueue\Client;

use Illuminate\Contracts\Events\Dispatcher;
use Interop\Queue\Context;
use Interop\Queue\Destination;

abstract class BaseClient
{
    protected Context $context;
    protected Dispatcher $events;
    protected Destination $destination;
    protected array $config = [];

    public function __construct(Context $context, Destination $destination, Dispatcher $events, array $config = [])
    {
        $this->context = $context;
        $this->destination = $destination;
        $this->events = $events;
        $this->config = array_merge($this->config, $config);

        $this->init();
    }

    protected function init(): void
    {
    }
}
