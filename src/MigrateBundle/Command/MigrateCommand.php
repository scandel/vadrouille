<?php

namespace MigrateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
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
           ->addOption(
                'update',
                null,
                InputOption::VALUE_NONE,
                'If set, the migration will update entries if already existing'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $migrationClass = '\\MigrateBundle\Migrations\\'.$input->getArgument('migration');
        $update = ($input->getOption('update')) ? true:false;

        if (class_exists($migrationClass)) {
            if (is_subclass_of($migrationClass, '\\MigrateBundle\\Model\\Migration')) {
                $output->writeln("<info>Début de la migration classe " . $migrationClass."</info>");

                $destinationConnection = $this->getContainer()->get('database_connection');
                $sourceConnection = $this->getContainer()->get('doctrine.dbal.source_connection');

                $migration = new $migrationClass();
                $migration->setDestinationConnection($destinationConnection);
                $migration->setSourceConnection($sourceConnection);
                $migration->import($update);
                $output->writeln("<info>Fin de la migration classe " . $migrationClass."</info>");

                $errors = $migration->getErrors();
                if (count($errors) == 0) {
                    $output->writeln("<info>Tout s'est bien passé !</info>");
                }
                else {
                    $output->writeln("<error>".count($errors)." erreurs :</error>");
                    $table = new Table($output);
                    $table->setHeaders(array('Id','Erreur'));
                    $tableErrors = array();
                    foreach ($errors as $id => $err) {
                        $tableErrors[] = array($id, $err);
                    }
                    $table->setRows($tableErrors);
                    $table->render();
                }

                $report = $migration->report ;
                $tableReport = array();
                foreach ($report as $label => $nb) {
                    $tableReport[] = array($label, $nb);
                }
                $table2 = new Table($output);
                $table2->setRows($tableReport);
                $table2->render();

            }
        }
    }
}