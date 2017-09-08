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
        $table = (new Table('test'))->addColumn('test', 'varchar');
        $builder = new TableBuilder();
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `test` (
`test` varchar(200) NOT NULL DEFAULT "" COMMENT ""
) ENGINE InnoDB CHARSET utf8 COMMENT "";
SQL;
        $this->assertEquals($sql, $builder->create($table)->getTableInfo());
    }

    public function testAlter()
    {
        $table = (new Table('test'))->addColumn('test', 'varchar');
        $builder = new TableBuilder();
        $sql = <<<SQL
ALTER TABLE `test` ADD `test` varchar(200) NOT NULL DEFAULT "" COMMENT "";
SQL;
        $this->assertEquals($sql, $builder->alter($table)->getTableInfo());
    }
}
