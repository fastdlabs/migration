<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace FastD\Migration;

/**
 * Class Migration
 * @package FastD\Migration
 */
abstract class MigrationAbstract
{
    /**
     * @return Table
     */
    abstract public function setUp();

    /**
     * @return mixed
     */
    abstract public function dataSet();
}