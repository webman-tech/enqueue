<?php

namespace WebmanTech\Enqueue\Commands;

use Interop\Queue\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MakeJobCommand extends Command
{
    protected static $defaultName = 'make:job';
    protected static $defaultDescription = 'Make enqueue task';

    protected ?string $savePath = null;
    protected ?string $stub = null;

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Job name');
        $this->addOption('producer', '', InputOption::VALUE_NONE, 'Only producer');
        $this->addOption('consumer', '', InputOption::VALUE_NONE, 'only consumer');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $savePath = $this->savePath ?: config('plugin.webman-tech.enqueue.app.command_make.save_path', 'job');
        $this->stub = 'job.stub';
        if ($input->getOption('producer')) {
            $this->stub = 'job_producer.stub';
        } elseif ($input->getOption('consumer')) {
            $this->stub = 'job_consumer.stub';
        }

        $name = str_replace('\\', '/', $name);
        // 将 name 按照 / 分隔，并取最后一个
        $paths = explode('/', $name);
        $name = ucfirst(array_pop($paths));
        if (!str_ends_with($name, 'Job')) {
            $name .= 'Job';
        }
        if ($paths) {
            $savePath .= '/' . implode('/', $paths);
        }
        $namespace = 'app\\' . str_replace('/', '\\', $savePath);
        $file = app_path() . '/' . $savePath . '/' . $name . '.php';

        $output->writeln("Make Enqueue Job {$namespace}\\{$name}");

        $this->createFile($name, $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     * @return void
     */
    protected function createFile($name, $namespace, $file)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $content = strtr(file_get_contents(__DIR__. '/stubs/' . $this->stub), [
            '{{namespace}}' => $namespace,
            '{{name}}' => $name,
        ]);
        file_put_contents($file, $content);
    }
}
