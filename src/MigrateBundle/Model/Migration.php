<?php

namespace MigrateBundle\Model;

use Doctrine\DBAL\DBALException;
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
     *      destination => source
     * @var array
     */
    protected $mappings;
    private $wholeMappings;

    protected $disableAutoIncrement = false;

    protected $rowMin = 1;
    protected $rowMax = null;

    /**
     * @var Errors during insertion
     */
    protected $errors;

    /**
     * @var report of what happened during migration
     */
    public $report;

    public function __construct()
    {
        $this->mappings = array();
        $this->wholeMappings = array();
        $this->errors = array();
        $this->report = array(
            "inserted" => 0,
            "updated" => 0,
            "unchanged" => 0,
            "insert_errors" => 0,
            "update_errors" => 0,
        );

    }

    public function setDestinationConnection(Connection $destinationConnection)
    {
        $this->destinationConnection = $destinationConnection;
    }

    public function setSourceConnection(Connection $sourceConnection)
    {
        $this->sourceConnection = $sourceConnection;
    }

    public function import($update = false)
    {
        // Find automatic mappings and merge with user mappings
        $this->buildMappings();

        // Get entries from source
        $sql = "SELECT * FROM ".$this->sourceTable." WHERE id BETWEEN :rowMin AND :rowMax";
        $stmt = $this->sourceConnection->prepare($sql);
        $stmt->bindParam(':rowMin', $this->rowMin);
        $stmt->bindParam(':rowMax', $this->rowMax);
        $stmt->execute();

        // Loop over results
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            if ($row = $this->prepareRow($row)) {

                // Copy respecting mappings
                $insertRow = $this->map($row);
                // After mappings
                $this->prepare($insertRow,$row);

                // Insert or update ?
                $doUpdate = false;
                if ($update) {
                    // Does the row already exists in the destination table ?
                    $sqlExists = "SELECT COUNT(*) FROM ".$this->destinationTable." WHERE id = :id";
                    $stmtExists = $this->destinationConnection->prepare($sqlExists);
                    $stmtExists->bindParam(':id', $row['id']);
                    $stmtExists->execute();

                    if ($stmtExists->fetchColumn(0) == 1) {
                        $doUpdate = true;
                    }

                    // Is the row  different from values we want to insert ?
                    // todo...
                }

                if ($doUpdate) {
                    // Update the destination table
                    try {
                        $this->destinationConnection->update($this->destinationTable, $insertRow, array("id" => $row["id"]));
                        $this->report['updated']++;
                    } catch (DBALException $e) {
                        $this->errors[$row["id"]] = $e->getMessage();
                        $this->report['update_errors']++;
                    }
                }
                else {
                    // Insert into destination table
                    try {
                        $this->destinationConnection->insert($this->destinationTable, $insertRow);
                        $this->report['inserted']++;
                    } catch (DBALException $e) {
                        $this->errors[$row["id"]] = $e->getMessage();
                        $this->report['insert_errors']++;
                    }
                }
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
        return $row;
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
        if (count($this->mappings) > 0) {
            $this->wholeMappings = array_merge($basicMappings, $this->mappings);
        } else {
            $this->wholeMappings = $basicMappings;
        }
    }

    public function getMappings()
    {
        $this->buildMappings();
        return $this->wholeMappings;
    }

    /**
     * Returns an array of fields in Source table, not mapped to anything
     */
    public function getNonMappedInSource()
    {
        $this->buildMappings();
        $sourceMappedColumns = array_values($this->wholeMappings);

        $sm2 = $this->sourceConnection->getSchemaManager();
        $columns2 = $sm2->listTableColumns($this->sourceTable);
        $sourceColumns = array_keys($columns2);

        return array_diff($sourceColumns, $sourceMappedColumns);

    }

    /**
     * Returns an array of fields in Destination table, not mapped to anything
     */
    public function getNonMappedInDestination()
    {
        $this->buildMappings();
        $destMappedColumns = array_keys($this->wholeMappings);

        $sm = $this->destinationConnection->getSchemaManager();
        $columns = $sm->listTableColumns($this->destinationTable);
        $destColumns = array_keys($columns);

        return array_diff($destColumns, $destMappedColumns);

    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Map source row to destination row, based on mappings
     *
     * @param $row
     * @return array
     */
    private function map($row) {

        $insertRow = array();

        foreach($this->wholeMappings as $destinationField => $sourceField) {
            if (array_key_exists($sourceField, $row)) {
                $insertRow[$destinationField] = $row[$sourceField];
            }
        }
        return $insertRow;
    }

}


