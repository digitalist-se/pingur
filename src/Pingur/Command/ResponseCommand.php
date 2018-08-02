<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\InputOption;
use GuzzleHttp;
use Snapshotpl\StatusCode\StatusCode;

class ResponseCommand extends Command
{

    protected $container;
    protected static $defaultName = 'response';

    public function __construct(ContainerBuilder $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> will check response.</info>';

        $this->setName("response")
        ->setDescription("check response for site")
        ->addUsage('--url=https://foo.com --pass=foo --user=bar --needle="text to find on page"')
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
                'pass',
                null,
                InputOption::VALUE_OPTIONAL,
                'password',
                null
            ),
            new InputOption(
                'user',
                null,
                InputOption::VALUE_OPTIONAL,
                'user',
                null
            ),
            new InputOption(
                'needle',
                null,
                InputOption::VALUE_OPTIONAL,
                'Needle to check for in haystack (url)',
                null
            ),
            ]
        )
        ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $url = $input->getOption('url');
        $needle = $input->getOption('needle');
        $user = $input->getOption('user');
        $pass = $input->getOption('pass');

        $client = new GuzzleHttp\Client();


        if (isset($pass) && isset($user)) {
            $response = $client->request('GET', "$url", ['auth' => [$user, $pass], 'http_errors' => false]);
        } else {
            $response = $client->request('GET', "$url", ['http_errors' => false]);
        }

        $status_code = $response->getStatusCode();

        $status_code_check = new StatusCode($status_code);

        $content_type =$response->getHeaderLine('content-type');
        $body = $response->getBody();
        $found_output = null;
        if (isset($needle)) {
            $found_output = "needle not found";
            if ($this->needleInHaystack($needle, $body) == true) {
                $found_output = "needle found";
            }
        }

        $output->writeln("<info>Response:\n" .
        "\tStatus code: $status_code_check\n" .
        "\tContent type: $content_type\n" .
        "\tHaystack: $found_output\n" .
        "</info>");
    }


  /**
   * @param string $needle
   * @param string $body
   *
   * @return bool
   */
    public function needleInHaystack($needle, $body)
    {
        if (strpos($body, $needle) !== false) {
            return true;
        } else {
            return false;
        }
    }
}
