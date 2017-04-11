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
 * Class Field
 * @package FastD\Database\Schema
 */
class Field
{
    use Rename;

    /**
     * int
     */
    const BIT = 'bit';
    const BOOL = 'bool';
    const TINY_INT = 'tinyint';
    const SMALL_INT = 'smallint';
    const MEDIUM_INT = 'mediumint';
    const INT = 'int';
    const BIG_INT = 'bigint';

    /**
     * float
     */
    const FLOAT = 'float';
    const DOUBLE = 'double';
    const DECIMAL = 'decimal';

    /**
     * char
     */
    const CHAR = 'char';
    const VARCHAR = 'varchar';
    const TEXT = 'text';
    const TINY_TEXT = 'tinytext';
    const MEDIUM_TEXT = 'mediumtext';
    const LONG_TEXT = 'longtext';
    const TINY_BLOB = 'tinyblob';
    const BLOB = 'blob';
    const MEDIUM_BLOB = 'mediumblob';
    const LONG_BLOB = 'longblob';

    /**
     * date
     */
    const DATE = 'date';
    const DATETIME = 'datetime';
    const TIMESTAMP = 'timestamp';
    const TIME = 'time';
    const YEAR = 'year';

    /**
     * other
     */
    const BINARY = 'binary';
    const VARBINARY = 'varbinary';
    const ENUM = 'emum';
    const SET = 'set';
    const GEOMETRY = 'geometry';
    const POINT = 'point';
    const MULTIPOINT = 'multipoint';
    const LINESTRING = 'linestring';
    const MULTILINESTRING = 'multilinestring';
    const POLYGON = 'polygon';
    const GEOMETRYCOLLECTION = 'geometrycollection';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $alias;

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
     * @param $name
     * @param $type
     * @param $length
     * @param bool $nullable
     * @param string $default
     * @param string $comment
     */
    public function __construct($name, $type, $length, $nullable = false, $default = '', $comment = '')
    {
        $this->name = $name;

        $this->type = $this->getFiledType($type);

        $this->length = $length;

        $this->nullable = $nullable;

        $this->default = $this->getFieldTypeDefault($type, $default);

        $this->comment = $comment;
    }

    /**
     * @param $type
     * @param $default
     * @return int|string
     */
    protected function getFieldTypeDefault($type, $default)
    {
        if (in_array($type, [
            'int',
            'smallint',
            'tinyint',
            'mediumint',
            'integer',
            'bigint',
            'float',
            'double'])) {
            return empty($default) ? 0 : (int) $default;
        }

        return empty($default) ? '' : (string) $default;
    }

    /**
     * @param $type
     * @return string
     */
    protected function getFiledType($type)
    {
        if (in_array($type, ['array', 'json'])) {
            $type = 'varchar';
        }

        return $type;
    }

    /**
     * @return int
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
    public function setUnsigned($unsigned)
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
    public function setNullable($nullable)
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
    public function setDefault($default)
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
    public function setComment($comment)
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
     * @param Key $key
     * @return $this
     */
    public function setKey(Key $key)
    {
        $this->key = $key;
        
        $key->setField($this);

        return $this;
    }
    
    /**
     * @return string
     */
    public function getAlias()
    {
        return empty($this->alias) ? $this->rename($this->name) : $this->alias;
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

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
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        if (null === $this->key) {
            return false;
        }

        return $this->key->isPrimary();
    }

    /**
     * @return bool
     */
    public function isIndex()
    {
        if (null === $this->key) {
            return false;
        }

        return $this->key->isIndex();
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
        if (null === $this->key) {
            return false;
        }
        
        return $this->key->isUnique();
    }

    /**
     * @param int $inc
     * @return $this
     */
    public function setIncrement($inc = 1)
    {
        $this->increment = $inc;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncrement()
    {
        return null !== $this->increment;
    }

    /**
     * @param Field $field
     * @return bool
     */
    public function equal(Field $field)
    {
        return
            (
                $field->getName()
                . $field->getType()
                . $field->getLength()
                . $field->getComment()
                . $field->getDefault()
                . (null !== $field->getKey() ? $field->getKey()->getKey() : '')
            ) ===
            (
                $this->getName()
                . $this->getType()
                . $this->getLength()
                . $this->getComment()
                . $this->getDefault()
                . (null !== $this->getKey() ? $this->getKey()->getKey() : '')
            );
    }
}