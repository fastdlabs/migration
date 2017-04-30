<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

use FastD\Migration\Column;
use FastD\Migration\Table;
use FastD\Migration\TableBuilder;

class TableBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $table = (new Table('test'))->addColumn(new Column('test', 'varchar'));
        $builder = new TableBuilder();
        echo PHP_EOL;
        echo $builder->create($table)->getTableInfo();
    }

    public function testAlter()
    {
        $table = (new Table('test'))->addColumn(new Column('test', 'varchar'));
        $builder = new TableBuilder();
        echo PHP_EOL;
        echo $builder->alter($table)->getTableInfo();
    }
}
