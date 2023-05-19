<?php

namespace WebmanTech\Enqueue\Exceptions;

interface AckSupportExceptionInterface
{
    /**
     * @return string
     */
    public function getAck(): string;
}
