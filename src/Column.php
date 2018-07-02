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
 * Class Column
 * @package FastD\Migration
 */
class Column
{
    /**
     * @var array
     */
    protected $columns = [
        'bit' => ['bit', 10, 0],
        'bool' => ['boo', 1, 0],
        'tinyint' => ['tinyint', 4, 0],
        'smallint' => ['smallint', 6, 0],
        'mediumint' => ['mediumint', 8, 0],
        'int' => ['int', 11, 0],
        'bigint' => ['bigint', 11, 0],
        'float' => ['float', 10, 0],
        'double' => ['double', 10, 0],
        'decimal' => ['decimal', 1, 0],
        'char' => ['char', 100, ''],
        'varchar' => ['varchar', 200, ''],
        'text' => ['text', null, null],
        'tinytext' => ['tinytext', null, null],
        'mediumtext' => ['mediumtext', null, null],
        'longtext' => ['longtext', null, null],
        'tinyblob' => ['tinyblob', null, null],
        'blob' => ['blob', null, null],
        'mediumblob' => ['mediumblob', null, null],
        'longblob' => ['longblob', null, null],
        'date' => ['date', 1, 0],
        'datetime' => ['datetime', null, 'NOW()'],
        'timestamp' => ['timestamp', null, 'CURRENT_TIMESTAMP'],
        'time' => ['time', 1, 0],
        'year' => ['year', 1, 0],
        'binary' => ['binary', 1, 0],
        'varbinary' => ['varbinary', 1, 0],
        'emum' => ['emum', [], 0],
        'set' => ['set', 1, 0],
        'geometry' => ['geometry', 1, 0],
        'point' => ['point', 1, 0],
        'multipoint' => ['multipoint', 1, 0],
        'linestring' => ['linestring', 1, 0],
        'multilinestring' => ['multilinestring', 1, 0],
        'polygon' => ['polygon', 1, 0],
        'geometrycollection' => ['geometrycollection', 1, 0],
        'json' => ['varchar', 200, ''],
        'enum' => ['enum', 200, ''],
        'array' => ['varchar', 200, ''],
    ];

    protected $name;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $unsigned = false;

    /**
     * @var bool
     */
    protected $nullable = false;

    /**
     * @var string
     */
    protected $default;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var Key
     */
    protected $key;

    /**
     * @var null|int
     */
    protected $increment = null;

    /**
     * Field constructor.
     *
     * @param $columnName
     * @param $type
     * @param $length
     * @param bool $nullable
     * @param string $default
     * @param string $comment
     */
    public function __construct($columnName, $type, $length = null, $nullable = false, $default = '', $comment = '')
    {
        $type = strtolower($type);

        if (!array_key_exists($type, $this->columns)) {
            throw new \LogicException(sprintf('unknown data type %s', $type));
        }

        list(, $defaultLength, $defaultValue) = $this->columns[$type];

        $this->name = $columnName;

        $this->type = $type;

        $this->setLength($length, $defaultLength);

        $this->nullable = $nullable;

        $this->default = empty($default) ? $defaultValue : $default;

        $this->comment = $comment;
    }

    /**
     * @param string|int $length
     */
    public function setLength($length = null, $defaultLength = null)
    {
        if (is_array($length)) {
            $this->length = '';
            foreach ($length as $value) {
                '' !== $this->length && $this->length .= ',';
                $this->length .= is_numeric($value) ? $value : '\'' . $value . '\'';
            }
        } elseif (empty($length)) {
            $this->length = $defaultLength;
        } else {
            $this->length = $length;
        }
    }

    /**
     * @return int|null|string
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * @param boolean $unsigned
     * @return $this
     */
    public function withUnsigned($unsigned)
    {
        $this->unsigned = $unsigned;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @param boolean $nullable
     * @return $this
     */
    public function withNullable($nullable)
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $default
     * @return $this
     */
    public function withDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function withComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function withKey($key)
    {
        $this->key = new Key($key);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function withName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return null === $this->key ? false : $this->key->isPrimary();
    }

    /**
     * @return bool
     */
    public function isIndex()
    {
        return null === $this->key ? false : $this->key->isIndex();
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
        return null === $this->key ? false : $this->key->isUnique();
    }

    /**
     * @param int $inc
     * @return $this
     */
    public function withIncrement($inc = 1)
    {
        $this->increment = $inc;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @return bool
     */
    public function isIncrement()
    {
        return null !== $this->increment;
    }

    /**
     * @param Column $column
     * @return bool
     */
    public function equal(Column $column)
    {
        return
            (
                $column->getName()
                . $column->gettype()
                . $column->getLength()
                . $column->getComment()
                . $column->getDefault()
                . (null !== $column->getKey() ? $column->getKey()->getKey() : '')
            ) ===
            (
                $this->getName()
                . $this->gettype()
                . $this->getLength()
                . $this->getComment()
                . $this->getDefault()
                . (null !== $this->getKey() ? $this->getKey()->getKey() : '')
            );
    }

    public function __toString()
    {

        // name type(10) unsigned not null default '' comment ''
        return sprintf(
            '`%s` %s%s %s %s default %s comment %s',
            $this->getName(),
            $this->gettype(),
            (empty($this->getLength()) ? '' : '(' . $this->getLength() . ')'),
            ($this->isUnique() ? 'unsigned' : ''),
            ($this->isNullable() ? '' : 'not null'),
            ('\'' . $this->getDefault() . '\''),
            ('\'' . $this->getComment() . '\'')
        );
    }
}
