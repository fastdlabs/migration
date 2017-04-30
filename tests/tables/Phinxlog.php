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
            ->addColumn(new Column('version', 'bigint', 20, false, '0', ''))
            ->addColumn(new Column('migration_name', 'varchar', 100, true, '', ''))
            ->addColumn(new Column('start_time', 'timestamp', null, true, 'CURRENT_TIMESTAMP', ''))
            ->addColumn(new Column('end_time', 'timestamp', null, true, 'CURRENT_TIMESTAMP', ''))
            ->addColumn(new Column('breakpoint', 'tinyint', 1, false, '0', ''))
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