<?php

use WebmanTech\Enqueue\Commands\ConsumerRunCommand;
use WebmanTech\Enqueue\Commands\MakeJobCommand;

return [
    MakeJobCommand::class,
    ConsumerRunCommand::class,
];
