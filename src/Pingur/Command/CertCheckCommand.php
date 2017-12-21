<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\InputOption;
use Spatie\SslCertificate\SslCertificate;
use DateTime;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Yaml\Yaml;

class CertCheckCommand extends Command
{

    protected $container;
    protected static $defaultName = 'cert:check';

    public function __construct(ContainerBuilder $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $HelpText = 'The <info>%command.full_name%</info> will check SSL cert for provided url.';
        $this->setName("cert:check")
        ->setAliases(['cc'])
        ->setDescription("check a SSL cert for a site")
        ->addUsage('--domain=foobar.com')
        ->setDefinition(
            [
            new InputOption(
                'domain',
                'd',
                InputOption::VALUE_OPTIONAL,
                'URL to check',
                null
            ),
            ]
        )
        ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $url = $input->getOption('domain');
        $check = SslCertificate::createForHostName($url);
        $issuer = $check->getIssuer();
        $algorithm = $check->getSignatureAlgorithm();
        $expiration = $check->expirationDate();
        $domain = $check->getDomain();
        $additional = null;

        $config_file = '.pingur/config.yml';
        $running_path = getcwd();
        $config = Yaml::parseFile("$running_path/$config_file");


        $additional_domains = $check->getAdditionalDomains();
        if (count($additional_domains)>1) {
            $domains = implode(', ', $additional_domains);
        }
        if (isset($domains)) {
            $additional = "\n\tAdditional domains: $domains";
        }

        // calculate time.
        $currentTime = new DateTime("now");
        $expirationTime = new DateTime($expiration);
        $difference = $currentTime->diff($expirationTime);

        // get days before the cert expire that we should warn
        $days = $config['cert']['warning'];

        if($expirationTime->diff($currentTime)->days <= $days)
        {
            $tell = "Cert for $url expires in $difference->days days";
            $command = $this->getApplication()->find('slack');
            $arguments = array(
                    'command' => 'cert:check',
                    '--endpoint' => $config['slack']['endpoint'],
                    '--message' => $tell,
            );
            $greetInput = new ArrayInput($arguments);
            $returnCode = $command->run($greetInput, $output);

        }

        $output->writeln("<info>Cert:\n" .
        "\tIssuer: $issuer\n" .
        "\tAlgorithm: $algorithm\n" .
        "\tExpiration: $expiration (in $difference->days days)\n" .
        "\tDomain: $domain" .
        "$additional" .
        "</info>");
    }
}
