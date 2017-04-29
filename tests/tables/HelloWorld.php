<?php

use \FastD\Migration\Migration;
use \FastD\Migration\Column;
use \FastD\Migration\Table;


class HelloWorld extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $table = new Table('hello_world');

        $table
            ->addColumn(new Column('id', 'int'))
            ->addColumn(new Column('content', 'varchar'))
            ->addColumn(new Column('user', 'varchar'))
            ->addColumn(new Column('created', 'datetime'))
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