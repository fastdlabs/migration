<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Database\Schema\Structure;

/**
 * Class Key
 * 
 * @package FastD\Database\Schema
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
     * @var string
     */
    protected $field;

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
        $this->key = $key;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function setField(Field $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getField()
    {
        return $this->field;
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
        return $this->key === Key::PRIMARY;
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
        return $this->key === Key::UNIQUE;
    }

    /**
     * @return bool
     */
    public function isIndex()
    {
        return $this->key === Key::INDEX;
    }
}