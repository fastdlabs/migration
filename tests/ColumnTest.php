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

//        echo $column;
    }
}
