<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

use FastD\Migration\Column;
use FastD\Migration\Schema;
use FastD\Migration\Table;

class MigrationTest extends PHPUnit_Framework_TestCase
{
    protected $pdo;
    protected $table;

    public function setUp()
    {
        $this->pdo = new PDO('mysql:dbname=ci', 'root');
        $this->table = new Table('hello');
        $this->table->addColumn('created', 'datetime')->setDefault('default');
        $this->table->addColumn('updated', 'datetime');
    }

    public function testMigrationCreateTableSchema()
    {
        $migration = new Schema($this->pdo);
//        echo $migration->create($this->table) . PHP_EOL;
//        echo $migration->create($this->table, true);
    }

    public function testMigrationAlterTableSchema()
    {
        $migration = new Schema($this->pdo);
//        echo $migration->alter($this->table);
    }

    public function testMigrationUpdateTableSchema()
    {
        $migration = new Schema($this->pdo);
        $this->table->addColumn(new Column('date', 'int', 10));
        echo $migration->update($this->table);
    }
}
