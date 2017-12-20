<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputOption;
use Spatie\SslCertificate\SslCertificate;
use DateTime;

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
        $HelpText = 'The <info>%command.name%</info> will check SSL cert for provided url.';
        $this->setName("cert:check")
        ->setDescription("check cert for a site")
        ->addUsage('--url=https://foobar.com')
        ->setDefinition(
            [
            new InputOption(
                'url',
                'u',
                InputOption::VALUE_OPTIONAL,
                'URL to check',
                null
            ),
            new InputOption(
                'delimiter',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Delimiter to display additinal domains',
                ','
            ),
            ]
        )
        ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $url = $input->getOption('url');
        $delimiter = $input->getOption('delimiter');
        $check = SslCertificate::createForHostName($url);
        $issuer = $check->getIssuer();
        $algorithm = $check->getSignatureAlgorithm();
        $expiration = $check->expirationDate();
        $domain = $check->getDomain();
        $before = null;
        $additional = null;
      // This is for using acme.sh
      // https://gitlab.wklive.net/wk-public/jelastic-lb-acme
        if ($delimiter == ' -d ') {
            $before = '-d';
        }

        $additional_domains = $check->getAdditionalDomains();
        if (count($additional_domains)>1) {
            $domains = implode($delimiter, $additional_domains);
        }
        if (isset($domains)) {
            $additional = "\n\tAdditional domains: $before$domains";
        }

        $currentTime = new DateTime("now");
        $expirationTime = new DateTime($expiration);
        $difference = $currentTime->diff($expirationTime);

        $output->writeln("<info>Cert:\n" .
        "\tIssuer: $issuer\n" .
        "\tAlgorithm: $algorithm\n" .
        "\tExpiration: $expiration (in $difference->days days)\n" .
        "\tDomain: $domain" .
        "$additional" .
        "</info>");
    }
}
