<?php

use \FastD\Migration\MigrationAbstract;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class Hello extends MigrationAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $table = new Table('hello');

        $table
            ->addColumn('id', 'INT', 11, false, '0', '')
            ->addColumn('content', 'VARCHAR', 255, false, '', '')
            ->addColumn('user', 'VARCHAR', 255, false, '', '')
            ->addColumn('created', 'DATETIME', null, false, 'NOW()', '')
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