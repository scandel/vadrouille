<?php

namespace MigrateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MigrateBundle\Migrations;
use MigrateBundle\Model;

class MigrateCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('migrate:table')
            ->setDescription('Migrate stuff into database')
            ->addArgument(
                'migration',
                InputArgument::OPTIONAL,
                'Name of the migration class'
            )
           /* ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )*/
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $migrationClass = '\\MigrateBundle\Migrations\\'.$input->getArgument('migration');

        if (class_exists($migrationClass)) {
            if (is_subclass_of($migrationClass, '\\MigrateBundle\\Model\\Migration')) {
                $output->writeln("Début de la migration classe " . $migrationClass);

                $destinationConnection = $this->getContainer()->get('database_connection');
                $sourceConnection = $this->getContainer()->get('doctrine.dbal.source_connection');

                $migration = new $migrationClass();
                $migration->setDestinationConnection($destinationConnection);
                $migration->setSourceConnection($sourceConnection);
                $migration->import();
                $output->writeln("Fin de la migration classe " . $migrationClass);
            }
        }
    }
}