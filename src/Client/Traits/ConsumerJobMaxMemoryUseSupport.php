<?php

namespace WebmanTech\Enqueue\Client\Traits;

trait ConsumerJobMaxMemoryUseSupport
{
    private ?string $defaultMemoryLimit = null;

    /**
     * 设置任务最大使用内存
     * @param int|null $maxMemoryUse
     */
    protected function setJobMaxMemoryUse(?int $maxMemoryUse)
    {
        if ($maxMemoryUse === null || $maxMemoryUse < 0) {
            return;
        }

        if ($this->defaultMemoryLimit === null) {
            $this->defaultMemoryLimit = ini_get('memory_limit');
        }

        if ($maxMemoryUse === 0) {
            ini_set('memory_limit', '-1');
            return;
        }
        $used = $this->memoryHasUsed(); // 需要加上脚本启动时已经耗费的内存
        ini_set('memory_limit', $maxMemoryUse + $used . 'M');
    }

    /**
     * 重置
     */
    protected function resetJobMaxMemoryUse()
    {
        if ($default = $this->defaultMemoryLimit) {
            if (substr($default, -1) === 'G') {
                $default = intval($default) * 1024;
            } else {
                $default = intval($default);
            }
            if ($this->memoryHasUsed() >= $default) {
                // 若当前使用内存已经超过默认，则不设置
                return;
            }
            ini_set('memory_limit', $this->defaultMemoryLimit);
        }
    }

    /**
     * @return int
     */
    private function memoryHasUsed(): int
    {
        return intval(ceil(memory_get_usage(true) / 1024 / 1024));
    }
}
