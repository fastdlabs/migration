<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

use FastD\Migration\Key;

class IndexTest extends PHPUnit_Framework_TestCase
{
    public function testPrimary()
    {
        $key = new Key('primary');
        $this->assertTrue($key->isPrimary());
        $this->assertFalse($key->isUnique());
        $this->assertFalse($key->isIndex());
    }

    public function testUnique()
    {
        $key = new Key('unique');
        $this->assertFalse($key->isPrimary());
        $this->assertTrue($key->isUnique());
        $this->assertFalse($key->isIndex());
    }

    public function testIndex()
    {
        $key = new Key('index');
        $this->assertFalse($key->isPrimary());
        $this->assertFalse($key->isUnique());
        $this->assertTrue($key->isIndex());
    }
}
