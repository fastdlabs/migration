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
 * Class Key
 * @package FastD\Migration
 */
class Key
{
    /**
     * Primary key
     *
     * @const
     */
    const PRIMARY = 'PRIMARY';

    /**
     * Unique key
     *
     * @const
     */
    const UNIQUE = 'UNIQUE';

    /**
     * Simple index
     *
     * @const
     */
    const INDEX = 'INDEX';

    /**
     * @var int
     */
    protected $key;

    /**
     * Key constructor.
     * @param string $key
     */
    public function __construct($key = Key::INDEX)
    {
        $this->key = strtoupper($key);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return $this->key === static::PRIMARY;
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
        return $this->key === static::UNIQUE;
    }

    /**
     * @return bool
     */
    public function isIndex()
    {
        return $this->key === static::INDEX;
    }
}
