<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\InputOption;
use lygav\slackbot\SlackBot;
use lygav\slackbot\Exceptions\SlackBotException;

class SlackCommand extends Command
{

    protected $container;
    protected static $defaultName = 'slack';

    public function __construct(ContainerBuilder $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> will send message to slack.
<comment>Samples:</comment>
<info>pingur %command.name% --message=foo --endpoint=https://hooks.slack.com/services/your/incoming/hook</info>';

        $this->setName("slack")
            ->setDescription("use slack to send message")
            ->setDefinition(
                [
                    new InputOption(
                        'message',
                        'm',
                        InputOption::VALUE_OPTIONAL,
                        'Message to send',
                        null
                    ),
                    new InputOption(
                        'endpoint',
                        'e',
                        InputOption::VALUE_OPTIONAL,
                        'Endpoint to use',
                        null
                    ),
                ]
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getOption('message');
        $endpoint = $input->getOption('endpoint');

        $slack_options = [
            'username' => 'Pingur',
            'icon_emoji' => ':broken_heart:',
            'icon_url' => 'https://d30y9cdsu7xlg0.cloudfront.net/png/22567-200.png',
            'channel' => '#se_pingdom',
            'as_user' => false,
        ];

        $slack = new SlackBot($endpoint, $slack_options);
        $slack->text($message);
        $slack->send();
    }
}
