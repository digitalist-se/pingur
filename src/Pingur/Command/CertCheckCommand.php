<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\InputOption;
use Spatie\SslCertificate\SslCertificate as Cert;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
use DateTime;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp;
use Pingur\Lib\Db;

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
        $check_if_cert_exist = false;
        $clean_input = parse_url($url);
        if (isset($clean_input['host'])) {
            $url = $clean_input['host'];
        }

        $config_file = '.pingur/config.yml';
        $running_path = getcwd();
        $config = Yaml::parseFile("$running_path/$config_file");

        if (isset($config['influxdb'])) {
            $influxdb = true;
        }

        $check_if_domain_has_record = $this->checkIfDomainExists($url);

        if ($check_if_domain_has_record === true) {
            $check_if_cert_exist = $this->certificateExists($url);
        } else {
            $tell = "Domain $url is down or does not exist\n";
            $this->sendSlack($config, $tell, $output);
            $output->writeln("<info>$url is down or does not exist</info>");

            $measurement = [];
            $measurement['tag'] = 'pingur';
            $measurement['domain'] = $url;
            $measurement['info']['host'] = gethostname();
            $measurement['info']['status'] = 'down';
            $measurement['info']['expiration'] = 0;
            $measurement['info']['issuer'] = '';
            $measurement['info']['created'] =  0;

            if ($influxdb === true) {
                $this->insertMeasurement($config['influxdb'], $measurement);
            }
        }

        if ($check_if_cert_exist === true) {
            $check = Cert::createForHostName($url);
            $issuer = $check->getIssuer();
            $algorithm = $check->getSignatureAlgorithm();
            $expiration = $check->expirationDate();
            $domain = $check->getDomain();
            $additional = null;

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

            if ($expirationTime->diff($currentTime)->days <= $days) {
                $tell = "Cert for $url expires in $difference->days days";
                $this->sendSlack($config, $tell, $output);
            }


            $valid_from = $check->validFromDate();
            $measurement = [];
            $measurement['tag'] = 'pingur';
            $measurement['domain'] = $url;
            $measurement['info']['host'] = gethostname();
            $measurement['info']['status'] = 'active';
            $measurement['info']['expiration'] = strtotime($expiration);
            $measurement['info']['issuer'] = $issuer;
            $measurement['info']['created'] =  strtotime($valid_from);


            if ($influxdb === true) {
                $this->insertMeasurement($config['influxdb'], $measurement);
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

    public function checkIfDomainExists($url)
    {
        try {
            $client = new GuzzleHttp\Client();
            $response = $client->request('GET', "$url", ['http_errors' => false]);
            if (null !== $response->getHeader('Content-Type')) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    public function certificateExists($url)
    {
        try {
            $sslCertificate = Downloader::downloadCertificateFromUrl($url, 20);
            $seralize = serialize($sslCertificate);
            if (empty($seralize)) {
                return false;
            }
        } catch (CouldNotDownloadCertificate $e) {
            return false;
        }
        return true;
    }

    public function sendSlack($config, $tell, $output)
    {
        $command = $this->getApplication()->find('slack');
        $arguments = array(
            'command' => 'slack',
            '--endpoint' => $config['slack']['endpoint'],
            '--message' => $tell,
        );
        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);
    }
    public function insertMeasurement($config, $measurement)
    {
        $db = new Db;
        $db->publish($config, $measurement);
    }
}
