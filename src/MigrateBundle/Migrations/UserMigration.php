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

        // Auto-Increment (ne marche pas)
        $this->disableAutoIncrement = true;

        // Essais
        $this->rowMin = 111;
        $this->rowMax = $this->rowMin + 100 ;

        // Mappings (destination => source)
        $this->mappings['username'] = 'email';
        $this->mappings['username_canonical'] = 'email';
        $this->mappings['email_canonical'] = 'email';

        $this->mappings['last_login'] = 'last_connexion';

        $this->mappings['gender'] = 'sex';
        $this->mappings['bio'] = 'a_few_words';
    }

    /**
     * @param $row : Before insertRow is prepared, based on mappings
     * Must return true, or false to skip row.
     */
    public function prepareRow($row)
    {
        // On ne garde que les utilisateurs OK, PRE, et les BANNED
        // (les OLD et PARTNER sont dégagés). OLD, c'est pour des questions d'intégrité email.
        if (!in_array($row['status'], array('OK', 'PRE', 'BANNED'))) {
            return false;
        }

        // Conversion de valeurs
        if (empty($row['a_few_words'])) {
            $row['a_few_words'] = '';
        }

        $gender = array(
            'M' => 'm',
            'W' => 'w',
            'Y' => 'w',
            '' => 'm');
        $row['sex'] = $gender[$row['sex']];

        // Téléphone...




        return $row;
    }



}