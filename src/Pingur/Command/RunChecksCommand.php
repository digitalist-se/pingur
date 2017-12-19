<?php
namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\ArrayInput;


class RunChecksCommand extends Command {
  protected $container;
  protected static $defaultName = 'run:checks';

  public function __construct(ContainerBuilder $container) {
    parent::__construct();
    $this->container = $container;
  }

  protected function configure() {
    $HelpText = 'The <info>cert:check</info> will ping url.
<comment>Samples:</comment>
<info>pingur ping --url=http://foobar.com</info>';

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
  protected function execute(InputInterface $input, OutputInterface $output) {
    $file = $input->getOption('file');

    $checks = Yaml::parseFile($file);
    foreach($checks as $site => $settings) {
      echo $site;
      echo "\n" . '----------------------' . "\n";
      if($settings['https']) {
        $command = $this->getApplication()->find('cert:check');
        $arguments = array(
              'command' => 'cert:check',
              '--url' => $site,
          );
          $greetInput = new ArrayInput($arguments);
          $returnCode = $command->run($greetInput, $output);
      }
      $protocol = 'http://';
      $needle = null;
      if (isset($settings['needle'])) {
        $needle = $settings['needle'];
      }
      if($settings['url']) {
        if($settings['https']) {
          $protocol = 'https://';
        }
        $command = $this->getApplication()->find('response');
        $arguments = array(
              'command' => 'response',
              '--url' => $protocol . $site . '/' . $settings['url'],
              '--needle' => $needle,
          );
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
      //var_dump($settings[0]['https']);
    }
  }
}
