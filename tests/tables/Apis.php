<?php

use \FastD\Migration\MigrationAbstract;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class Apis extends MigrationAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $table = new Table('apis');

        $table
            ->addColumn('id', 'INT', 11, false, '0', '')
            ->addColumn('api_id', 'INT', 11, false, '0', '')
            ->addColumn('key', 'VARCHAR', 60, false, '', '')
            ->addColumn('type', 'VARCHAR', 30, false, '', '')
            ->addColumn('is_available', 'TINYINT', 1, false, '0', '')
            ->addColumn('created_at', 'DATETIME', null, false, 'NOW()', '')
            ->addColumn('updated_at', 'DATETIME', null, false, 'NOW()', '')
            ->addColumn('user_id', 'INT', 11, false, '0', '')
            ->addColumn('title', 'VARCHAR', 100, false, '', '')
            ->addColumn('description', 'VARCHAR', 200, false, '', '')
            ->addColumn('path', 'VARCHAR', 200, false, '', '')
            ->addColumn('version', 'VARCHAR', 20, false, '', '')
        ;

        return $table;
    }

    /**
     * {@inheritdoc}
     */
    public function dataSet()
    {
        
    }
}