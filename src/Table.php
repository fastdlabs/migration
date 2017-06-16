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
 * @package FastD\Migration
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
     * Table constructor.
     * @param string $table
     * @param string $comment
     */
    public function __construct($table, $comment = '')
    {
        $this->table = $table;

        $this->withComment($comment);
    }

    /**
     * Setting columns.
     *
     * @param Column[] $columns
     * @return $this
     */
    public function withColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param $column
     * @param string $type
     * @param null $length
     * @param bool $nullable
     * @param string $default
     * @param string $comment
     * @return Table
     */
    public function addColumn($column, $type = 'varchar', $length = null, $nullable = false, $default = '', $comment = '')
    {
        $column = new Column($column, $type, $length, $nullable, $default, $comment);

        $this->columns[$column->getName()] = $column;

        return $this;
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
     * @return Column|bool
     */
    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : false;
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
    public function withCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @param $comment
     * @return $this
     */
    public function withComment($comment)
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
}