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
 *
 * @package FastD\Database\Schema
 */
class Index
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
    public function __construct($key = Index::INDEX)
    {
        $this->key = $key;
    }

    /**
     * @param Column $field
     * @return $this
     */
    public function withColumn(Column $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getColumn()
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
        return $this->key === Index::PRIMARY;
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
        return $this->key === Index::UNIQUE;
    }

    /**
     * @return bool
     */
    public function isIndex()
    {
        return $this->key === Index::INDEX;
    }
}