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
     * @var Column[]
     */
    protected $columns = [];

    /**
     * @var Column[]
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
     * Setting columns.
     *
     * @param Column[] $columns
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Add column.
     *
     * @param Column $column
     * @param Index $key
     * @return $this
     */
    public function addColumn(Column $column, Index $key = null)
    {
        if (null !== $key) {
            $column->setKey($key);
        }

        $this->columns[$column->getName()] = $column;

        return $this;
    }

    /**
     * Drop column.
     *
     * @param string $name
     * @return $this
     */
    public function dropColumn($name)
    {
        $this->drops[$name] = $name;

        return $this;
    }

    /**
     * Change column
     *
     * @param $name
     * @param Column $column
     * @param Index $key
     * @return $this
     */
    public function alterColumn($name, Column $column, Index $key = null)
    {
        if (null !== $key) {
            $column->setKey($key);
        }

        $this->columns[$name] = $column;

        $this->alters[$name] = $column;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getAlterColumns()
    {
        return $this->alters;
    }

    /**
     * @return array
     */
    public function getDropColumns()
    {
        return $this->drops;
    }

    /**
     * Get all table schema columns.
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get one table column object.
     *
     * @param $name
     * @return Column|null
     */
    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
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
        return $this->prefix.$this->table.$this->suffix;
    }
}