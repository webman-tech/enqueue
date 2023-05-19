<?php

namespace WebmanTech\Enqueue\Client\ConsumerClient;

use Interop\Queue\Message;
use WebmanTech\Enqueue\Job\BaseConsumerJob;

class NotSupportMessageJob extends BaseConsumerJob
{
    /**
     * @inheritDoc
     */
    public function handle(Message $message)
    {
        echo '[NotSupportMessage]: ' . json_encode([
                'body' => $message->getBody(),
                'headers' => $message->getHeaders(),
                'properties' => $message->getProperties(),
            ], JSON_UNESCAPED_UNICODE) . PHP_EOL;

        return true;
    }
}