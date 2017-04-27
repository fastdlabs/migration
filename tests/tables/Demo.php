<?php

/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */
class Demo extends \FastD\Migration\Migration
{
    public function setUp()
    {
        $table = new \FastD\Migration\Table('demo');

        $table->addColumn(new \FastD\Migration\Column('title', 'varchar'));

        return $table;
    }

    public function dataSet()
    {
        // TODO: Implement dataSet() method.
    }
}