<?php

namespace MigrateBundle\Model;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class Migration
{
    /**
     * @var Connection : connection to the origin database
     */
    protected $sourceConnection;

    /**
     * @var Connection : connection to the destination database
     */
    protected $destinationConnection;

    protected $sourceTable;

    protected $destinationTable;

    /**
     * Mappings must be defined beetween fields which do not wear the same name.
     * Fields wearing the same name in both tables will be automatically mapped together.
     * Exemple : email maps to email, no need to define it
     * But email_canonical must be mapped to email in the mappings array.
     *      source => destination
     * @var array
     */
    protected $mappings;
    private $wholeMappings;

    protected $disableAutoIncrement = false;

    protected $rowMin = 1;
    protected $rowMax = null;


    public function __construct()
    {
        $this->mappings = array();
        $this->wholeMappings = array();
    }

    public function setDestinationConnection(Connection $destinationConnection)
    {
        $this->destinationConnection = $destinationConnection;
    }

    public function setSourceConnection(Connection $sourceConnection)
    {
        $this->sourceConnection = $sourceConnection;
    }

    public function import()
    {
        // Find automatic mappings and merge with user mappings
        $this->buildMappings();

        // Get entries from source
        $sql = "SELECT * FROM :sourceTable WHERE id BETWEEN :rowMin AND :rowMax";
        $stmt = $this->sourceConnection->prepare($sql);
        $stmt->bindParam(':sourceTable', $this->sourceTable);
        $stmt->bindParam(':rowMin', $this->rowMin);
        $stmt->bindParam(':rowMax', $this->rowMax);
        $stmt->execute();

        // Loop over results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if ($this->prepareRow($row)) {

                // Copy respecting mappings
                $insertRow = $this->map($row);
                // After mappings
                $this->prepare($insertRow);

                // Insert into destination table
                $this->destinationConnection->insert($this->destinationTable, $insertRow);
            }
        }
    }

    /**
     * Executed BEFORE mappings are performed
     * Return false to skip row;
     * Add or remove (unset) row index if needed.
     *
     * @param $row
     * @return bool
     */
    protected function prepareRow($row) {
        return true;
    }

    /**
     * Executed BEFORE mappings are performed
     * Return false to skip row;
     * Add row index if needed.
     *
     * @param $row
     * @return bool
     */
    protected function prepare($insertRow) {
        return true;
    }

    /**
     * Find automatic mappings based on tables schemas,
     * and merge with user mappings.
     */
    private function buildMappings()
    {
        // Find common columns between source and destination

        $sm = $this->destinationConnection->getSchemaManager();
        $columns = $sm->listTableColumns($this->destinationTable);
        $destinationColumns = array_keys($columns);

        $sm2 = $this->sourceConnection->getSchemaManager();
        $columns2 = $sm2->listTableColumns($this->sourceTable);
        $sourceColumns = array_keys($columns2);

        $commonColumns = array_intersect($destinationColumns, $sourceColumns);

        // Mappings beetween same name columns
        $basicMappings = array_combine($commonColumns, $commonColumns);

        // Merge basic mappings with user mappings
        $this->wholeMappings = array_merge($basicMappings, $this->mappings);
    }


    /**
     * Map source row to destination row, based on mappings
     *
     * @param $row
     * @return array
     */
    private function map($row) {

        $insertRow = array();

        foreach($this->wholeMappings as $sourceField => $destinationField) {
            if (array_key_exists($sourceField, $row)) {
                $insertRow[$destinationField] = $row[$sourceField];
            }
        }
        return $insertRow;
    }

}


