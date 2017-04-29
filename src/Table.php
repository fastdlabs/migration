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
     * @param $column
     * @param $type
     * @return $this
     */
    public function addColumn($column, $type = 'varchar')
    {
        if (!($column instanceof Column)) {
            $column = new Column($column, $type);
        }

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
}