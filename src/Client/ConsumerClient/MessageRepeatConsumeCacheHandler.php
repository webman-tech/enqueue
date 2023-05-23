<?php

namespace WebmanTech\Enqueue\Client\ConsumerClient;

use Psr\SimpleCache\CacheInterface;

class MessageRepeatConsumeCacheHandler implements MessageRepeatConsumeInterface
{
    private CacheInterface $cache;
    private string $cachePrefix;
    /**
     * 记录缓存的时效，超时后清除，单位 s
     * @var int
     */
    private int $ttl;


    protected array $data = [];

    public function __construct(CacheInterface $cache, string $cachePrefix, int $ttl)
    {
        $this->cache = $cache;
        $this->cachePrefix = $cachePrefix;
        $this->ttl = $ttl;
    }

    /**
     * @inheritDoc
     */
    public function isConsumed(string $key): bool
    {
        return $this->cache->has($this->getCacheKey($key));
    }

    /**
     * @inheritDoc
     */
    public function markConsumeStatus(string $key, bool $isConsumed): void
    {
        if ($isConsumed) {
            $this->cache->set($this->getCacheKey($key), 1, $this->ttl);
        } else {
            $this->cache->delete($this->getCacheKey($key));
        }
    }

    /**
     * @param int $messageId
     * @return string
     */
    protected function getCacheKey(int $messageId): string
    {
        return $this->cachePrefix . $messageId;
    }
}