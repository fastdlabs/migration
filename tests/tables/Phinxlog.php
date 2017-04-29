<?php

use \FastD\Migration\Migration;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class Phinxlog extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $table = new Table('phinxlog');

        $table
            ->addColumn(new Column('version', 'bigint'))
            ->addColumn(new Column('migration_name', 'varchar'))
            ->addColumn(new Column('start_time', 'timestamp'))
            ->addColumn(new Column('end_time', 'timestamp'))
            ->addColumn(new Column('breakpoint', 'tinyint'))
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