<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputOption;
use JJG\Ping;

class PingCommand extends Command
{

    protected $container;
    protected static $defaultName = 'ping';

    public function __construct(ContainerBuilder $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $HelpText = 'The <info>ping</info> will ping url.
<comment>Samples:</comment>
<info>pingur ping --url=foobar.com</info>';

        $this->setName("ping")
        ->setDescription("ping a site")
        ->setDefinition(
            [
            new InputOption(
                'url',
                'u',
                InputOption::VALUE_OPTIONAL,
                'URL to ping',
                null
            ),
            ]
        )
        ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $host = $input->getOption('url');
        $ping = new Ping($host);
        $latency = $ping->ping();
        if ($latency !== false) {
            $output->writeln("<info>Latency:\n\t" . $latency . ' ms' . "</info>\n\n");
        } else {
            print 'Host could not be reached.';
        }
    }
}
