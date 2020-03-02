<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\InputOption;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Pingur\Lib\Crawler as Crawl;
use Pingur\Lib\TitleLogger;
use Spatie\Crawler\Crawler;


class CrawlCommand extends Command
{

    protected $container;
    protected static $defaultName = 'crawl';

    public function __construct(ContainerBuilder $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $HelpText = 'The <info>crawl</info> command will crawl given URL.
<comment>Samples:</comment>
<info>pingur crawl --domain=foobar.com</info>';

        $this->setName("crawl")
        ->setDescription("crawl URL")
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $host = $input->getOption('url');
        if (!$host) {
            $output->writeln("<error>You must supply the url</error>\n\n");
            return 1;
            exit;
        }

        $crawl = Crawler::create()
            ->setCrawlObserver( new Crawl() )
            ->startCrawling($host);

       // var_dump($crawl);     
        $output->writeln("<info>foo</info>\n\n");
    }
}
