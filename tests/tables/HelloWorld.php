<?php

use \FastD\Migration\MigrationAbstract;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class HelloWorld extends MigrationAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $table = new Table('hello_world');

        $table
            ->addColumn(new Column('id', 'int', 11, false, '0', ''))
            ->addColumn(new Column('content', 'varchar', 200, false, '', ''))
            ->addColumn(new Column('user', 'varchar', 200, false, '', ''))
            ->addColumn(new Column('created', 'datetime', null, false, 'CURRENT_TIMESTAMP', ''))
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