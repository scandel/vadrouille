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

class ShowMappingsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('migrate:show:mappings')
            ->setDescription('Show mappings for a migration')
            ->addArgument(
                'migration',
                InputArgument::OPTIONAL,
                'Name of the migration class'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $migrationClass = '\\MigrateBundle\Migrations\\'.$input->getArgument('migration');

        if (class_exists($migrationClass)) {
            if (is_subclass_of($migrationClass, '\\MigrateBundle\\Model\\Migration')) {
                $destinationConnection = $this->getContainer()->get('database_connection');
                $sourceConnection = $this->getContainer()->get('doctrine.dbal.source_connection');

                $migration = new $migrationClass();
                $migration->setDestinationConnection($destinationConnection);
                $migration->setSourceConnection($sourceConnection);

                $output->writeln("<info>Mappings définis pour " . $migrationClass. " :</info>");
                $table = new Table($output);
                $table->setHeaders(array('Destination','Source'));
                $tableMappings = array();
                foreach ($migration->getMappings() as $source => $dest) {
                    $tableMappings[] = array($source, $dest);
                }
                $table->setRows($tableMappings);
                $table->render();

                $output->writeln("\n<info>Champs non mappés dans la table source :</info>");
                $output->writeln(implode(', ',$migration->getNonMappedInSource()));

                $output->writeln("\n<info>Champs non mappés dans la table destination :</info>");
                $output->writeln(implode(', ',$migration->getNonMappedInDestination()));

            }
        }
    }
}