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


class CertCheckCommand extends Command {

  protected $container;

  public function __construct(ContainerBuilder $container) {
    parent::__construct();
    $this->container = $container;
  }

  protected function configure() {
    $HelpText = 'The <info>cert:check</info> will ping url.
<comment>Samples:</comment>
<info>pingur ping --url=http://foobar.com</info>';

    $this->setName("cert:check")
      ->setDescription("check cert for a site")
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

  protected function execute(InputInterface $input, OutputInterface $output) {

    $url = $input->getOption('url');
    $delimiter = $input->getOption('delimiter');
    $check = SslCertificate::createForHostName($url);
    $issuer = $check->getIssuer();
    $algorithm = $check->getSignatureAlgorithm();
    $expiration = $check->expirationDate();
    $domain = $check->getDomain();
    $before = null;
    //$addiontional_domains = null;
    $addiontional_domains = $check->getAdditionalDomains();
    if (is_array($addiontional_domains)) {
      $addiontional_domains = implode($delimiter, $addiontional_domains);
    }
    // This is for using acme.sh
    // https://gitlab.wklive.net/wk-public/jelastic-lb-acme
    if ($delimiter == ' -d ') {
      $before = '-d';
    }

    $output->writeln("<info>Cert:\n" .
      "\tIssuer: $issuer\n" .
      "\tAlgorithm: $algorithm\n" .
      "\tExpiration: $expiration\n" .
      "\tDomain: $domain\n" .
      "\tAdditional domains: $before $addiontional_domains" .
      "</info>");
  }
}
