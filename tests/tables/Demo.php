<?php

use \FastD\Migration\MigrationAbstract;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class Demo extends MigrationAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $table = new Table('demo');

        $table
            ->addColumn('id', 'INT', 10, false, '0', '')
            ->addColumn('content', 'VARCHAR', 255, false, '', '')
            ->addColumn('user', 'VARCHAR', 255, false, '', '')
            ->addColumn('created', 'DATETIME', null, false, 'NOW()', '')
            ->addColumn('name', 'VARCHAR', 20, false, '', '')
            ->addColumn('nickname', 'CHAR', 30, false, '', '')
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