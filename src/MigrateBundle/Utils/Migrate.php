<?php

namespace MigrateBundle\Utils;

use Doctrine\DBAL\Connection;

class Migrate
{
    /**
     * @var Connection $from_conn : connection to the origin database
     */
    protected $origin_conn;

    /**
     * @var Connection $to_conn : connection to the destination database
     */
    protected $dest_conn;

    public function __construct(Connection $origin_conn, Connection $dest_conn)
    {
        $this->dest_conn = $dest_conn;
        $this->origin_conn = $origin_conn;
    }

    public function getOrigin() {
        return $this->origin_conn;
    }

    public function getDest()
    {
        return $this->dest_conn;
    }

    // todo : determine methods
    // (tables org / dest mappings, copy one row, before row insert, after row insert,
    // before inserts, after inserts, ... etc...)
    // certaines méthodes doivent être abstraites


}


