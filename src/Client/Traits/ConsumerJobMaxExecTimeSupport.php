<?php

namespace WebmanTech\Enqueue\Client\Traits;

use RuntimeException;
use WebmanTech\Enqueue\Events\ConsumerJobTimeoutEvent;

trait ConsumerJobMaxExecTimeSupport
{
    private ?bool $supportAsyncSignals = null;
    private ?bool $listenedForSignal = null;

    protected function setJobMaxExecTime(?int $maxExecTime)
    {
        if ($maxExecTime === null || $maxExecTime <= 0) {
            return;
        }

        if ($this->supportAsyncSignals === null) {
            $this->supportAsyncSignals = $this->supportsAsyncSignals();
        }

        if ($this->supportAsyncSignals && $this->listenedForSignal === null) {
            $this->listenedForSignal = true;
            pcntl_async_signals(true);
        }

        if ($this->supportAsyncSignals) {
            pcntl_signal(SIGALRM, function () use ($maxExecTime) {
                $this->events->dispatch(new ConsumerJobTimeoutEvent($maxExecTime));

                if (extension_loaded('posix')) {
                    posix_kill(getmypid(), SIGKILL);
                }

                throw new RuntimeException('job timeout');
            });
            pcntl_alarm(max($maxExecTime, 0));
        }
    }

    protected function resetJobMaxExecTime()
    {
        if ($this->supportAsyncSignals) {
            pcntl_alarm(0);
        }
    }

    /**
     * @return bool
     */
    protected function supportsAsyncSignals(): bool
    {
        return extension_loaded('pcntl');
    }
}
