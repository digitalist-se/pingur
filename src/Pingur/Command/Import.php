<?php

namespace Pingur\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;

class ImportCommand extends \Doctrine\DBAL\Tools\Console\Command\ImportCommand
{

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();

        $doctrine = $container->get('doctrine');

        $em = $doctrine->getEntityManager();
        $db = $em->getConnection();

        $helperSet = $this->getHelperSet();
        $helperSet->set(new ConnectionHelper($db), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');

        parent::execute($input, $output);
    }
}
