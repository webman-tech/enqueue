<?php

namespace WebmanTech\Enqueue\Client\ConsumerClient;

class MessageRepeatConsumeMemoryHandler implements MessageRepeatConsumeInterface
{
    /**
     * 记录缓存的时效，超时后清除，单位 s
     * @var int
     */
    private int $ttl;

    protected array $data = [];

    public function __construct(int $ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @inheritDoc
     */
    public function isConsumed(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @inheritDoc
     */
    public function markConsumeStatus(string $key, bool $isConsumed): void
    {
        $this->flush();
        if ($isConsumed) {
            $this->data[$key] = time() + $this->ttl;
        } else {
            unset($this->data[$key]);
        }
    }

    protected function flush()
    {
        $now = time();
        foreach ($this->data as $key => $expireTime) {
            if ($expireTime <= $now) {
                unset($this->data[$key]);
                continue;
            }
            break;
        }
    }
}