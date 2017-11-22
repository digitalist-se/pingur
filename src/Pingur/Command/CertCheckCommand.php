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
<info>pingur ping --url=foobar.com</info>';

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
        ]
      )
      ->setHelp($HelpText);

  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $url = $input->getOption('url');
    $check = SslCertificate::createForHostName($url);
    $issuer = $check->getIssuer();
    $algorithm = $check->getSignatureAlgorithm();
    $expiration = $check->expirationDate();
    $domain = $check->getDomain();
    //$addiontional_domains = null;
    $addiontional_domains = $check->getAdditionalDomains();
    if (is_array($addiontional_domains)) {
      $addiontional_domains = implode(',', $addiontional_domains);
    }

    $output->writeln("<info>Cert:\n" .
      "\tIssuer: $issuer\n" .
      "\tAlgorithm: $algorithm\n" .
      "\tExpiration: $expiration\n" .
      "\tDomain: $domain\n" .
      "\tAdditional domains: $addiontional_domains" .
      "</info>");
  }
}
