<?php

namespace WebmanTech\Enqueue\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WebmanTech\Enqueue\Facades\Enqueue;

class ConsumerRunCommand extends Command
{
    protected static $defaultName = 'enqueue:run';
    protected static $defaultDescription = '执行一次消息消费';

    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, '需要消费的队列，definitions.consumer 的 key');
        $this->addOption('count', '', InputOption::VALUE_REQUIRED, '处理消息数', 0);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerClient = Enqueue::getManager()->createConsumerClient($input->getArgument('name'));
        $consumerClient->consume($input->getOption('count'));

        return self::SUCCESS;
    }
}