<?php
/**
 * Created by PhpStorm.
 * User: scandelier
 * Date: 22/12/15
 * Time: 15:14
 */

namespace MigrateBundle\Migrations;

use MigrateBundle\Model\Migration;

class UserMigration extends Migration
{

    public function __construct()
    {
        parent::__construct();

        // Tables
        $this->destinationTable = "Users";
        $this->sourceTable = "Users";

        // Mappings (source => destination)
        // $this->mappings[''] = '';

        // Auto-Increment (ne marche pas)
        $this->disableAutoIncrement = true;

        // Essais
        $this->rowMin = 100;
        $this->rowMax = $this->rowMin + 10 ;
    }

}