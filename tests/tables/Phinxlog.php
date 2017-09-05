<?php

use \FastD\Migration\MigrationAbstract;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class Phinxlog extends MigrationAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $table = new Table('phinxlog');

        $table
            ->addColumn('version', 'BIGINT', 20, false, '0', '')
            ->addColumn('migration_name', 'VARCHAR', 100, true, '', '')
            ->addColumn('start_time', 'TIMESTAMP', null, true, 'CURRENT_TIMESTAMP', '')
            ->addColumn('end_time', 'TIMESTAMP', null, true, 'CURRENT_TIMESTAMP', '')
            ->addColumn('breakpoint', 'TINYINT', 1, false, '0', '')
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