<?php
namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\ArrayInput;

class RunChecksCommand extends Command
{
    protected $container;
    protected static $defaultName = 'run:checks';

    public function __construct(ContainerBuilder $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> will run all checks.
<comment>Samples:</comment>
<info>pingur %command.name% --url=http://foobar.com</info>';

        $this->setName("run:checks")
        ->setDescription("run checks on sites in input file")
        ->setDefinition(
            [
            new InputOption(
                'file',
                'f',
                InputOption::VALUE_OPTIONAL,
                'File to read checks from',
                null
            ),
            ]
        )
        ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getOption('file');
        $running_path = getcwd();
        $checks = Yaml::parseFile("$running_path/$file");
        foreach ($checks as $site => $settings) {
            echo $site;
            echo "\n" . '----------------------' . "\n";
            if (isset($settings['https'])) {
                $command = $this->getApplication()->find('cert:check');
                $arguments = array(
                'command' => 'cert:check',
                '--domain' => $site,
                );
                $greetInput = new ArrayInput($arguments);
                $returnCode = $command->run($greetInput, $output);
            }
            $protocol = 'http://';
            $needle = null;
            if (isset($settings['needle'])) {
                $needle = $settings['needle'];
            }
            if (isset($settings['url'])) {
                if (isset($settings['https'])) {
                    $protocol = 'https://';
                }


                $command = $this->getApplication()->find('response');

                if (isset($settings['basic-auth']['pass']) && $settings['basic-auth']['user']) {
                    $pass = $settings['basic-auth']['pass'];
                    $user = $settings['basic-auth']['user'];
                    $arguments = array(
                    'command' => 'response',
                    '--url' => $protocol . $site . '/' . $settings['url'],
                    '--needle' => $needle,
                    '--user' => $user,
                    '--pass' => $pass,
                    );
                } else {
                    $arguments = array(
                    'command' => 'response',
                    '--url' => $protocol . $site . '/' . $settings['url'],
                    '--needle' => $needle,
                    );
                }

                $greetInput = new ArrayInput($arguments);
                $returnCode = $command->run($greetInput, $output);

                $command = $this->getApplication()->find('ping');
                $arguments = array(
                'command' => 'response',
                '--url' => $site,
                  );
                  $greetInput = new ArrayInput($arguments);
                  $returnCode = $command->run($greetInput, $output);
            }
          //$this->DoReport($error);
          //var_dump($settings[0]['https']);
        }
    }
    public function DoReport($error, $site)
    {

        $command = $this->getApplication()->find('slack');
        $arguments = array(
          'command' => 'slack',
          '--url' => $site,
        );
        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);
    }
}
