<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\InputOption;
use Spatie\Dns\Dns;

class DnsCommand extends Command
{

    protected $container;
    protected static $defaultName = 'check:dns';

    public function __construct(ContainerBuilder $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $HelpText = 'The <info>check:dns</info> will check DNS.
<comment>Samples:</comment>
<info>pingur check:dns --domain=foobar.com</info>';

        $this->setName("check:dns")
        ->setDescription("check dns for a domain")
        ->setDefinition(
            [
            new InputOption(
                'domain',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Domain to check',
                null
            ),
            ]
        )
        ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $host = $input->getOption('domain');
        if (!$host) {
            $output->writeln("<error>You must supply the host</error>\n\n");
            return 1;
            exit;
        }
        $dns = new Dns($host);
        //$dns->getRecords();

        $output->writeln("<info>" .  $dns->getRecords() . "</info>\n\n");
    }
}
