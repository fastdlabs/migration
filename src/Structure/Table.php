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
 * Class Table
 * @package FastD\Database\Schema
 */
class Table
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var Field[]
     */
    protected $fields = [];

    /**
     * @var Field[]
     */
    protected $alters = [];

    /**
     * @var array
     */
    protected $drops = [];

    /**
     * @var string
     */
    protected $charset = 'utf8';

    /**
     * @var string
     */
    protected $engine = 'InnoDB';

    /**
     * @var string
     */
    protected $comment = '';

    /**
     * @var string
     */
    protected $suffix = '';

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * Table constructor.
     * @param string $table
     * @param string $comment
     */
    public function __construct($table, $comment = '')
    {
        $this->table = $table;

        $this->setComment($comment);
    }

    /**
     * Setting fields.
     * 
     * @param Field[] $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Add field.
     *
     * @param Field $field
     * @param Key $key
     * @return $this
     */
    public function addField(Field $field, Key $key = null)
    {
        if (null !== $key) {
            $field->setKey($key);
        }
        
        $this->fields[$field->getName()] = $field;

        return $this;
    }

    /**
     * Drop field.
     *
     * @param string $name
     * @return $this
     */
    public function dropField($name)
    {
        $this->drops[$name] = $name;

        return $this;
    }

    /**
     * Change field
     *
     * @param $name
     * @param Field $field
     * @param Key $key
     * @return $this
     */
    public function alterField($name, Field $field, Key $key = null)
    {
        if (null !== $key) {
            $field->setKey($key);
        }

        $this->fields[$name] = $field;

        $this->alters[$name] = $field;

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getAlterFields()
    {
        return $this->alters;
    }

    /**
     * @return array
     */
    public function getDropFields()
    {
        return $this->drops;
    }

    /**
     * Get all table schema fields.
     *
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get one table field object.
     *
     * @param $name
     * @return Field|null
     */
    public function getField($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $engine
     * @return $this
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

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
     * @return string
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getFullTableName()
    {
        return $this->getPrefix() . $this->getTableName() . $this->getSuffix();
    }
}