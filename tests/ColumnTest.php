<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

use FastD\Migration\Column;

class ColumnTest extends PHPUnit_Framework_TestCase
{
    public function testColumn()
    {
        $column = new Column('test', 'varchar');

        $this->assertFalse($column->isIndex());
        $this->assertFalse($column->isUnique());
        $this->assertFalse($column->isPrimary());
        $this->assertFalse($column->isIncrement());
        $this->assertEquals('VARCHAR', $column->getType());
        $this->assertEmpty($column->getComment());
        $this->assertEmpty($column->getKey());
        $this->assertEquals('test', $column->getName());
        $this->assertEquals(200, $column->getLength());
        $this->assertFalse($column->isIncrement());
        $this->assertFalse($column->isNullable());
        $this->assertFalse($column->isUnsigned());
    }

    public function testColumnKey()
    {
        $column = new Column('test', 'varchar');
        $column->withKey('primary');
        $this->assertTrue($column->isPrimary());
        $this->assertFalse($column->isUnique());
        $this->assertFalse($column->isIndex());
        $this->assertNull($column->getIncrement());
    }

    public function testColumnIncrement()
    {
        $column = new Column('test', 'varchar');
        $column->withIncrement(1);
        $this->assertTrue($column->isIncrement());
        $this->assertEquals(1, $column->getIncrement());
    }
}
