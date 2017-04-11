<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Database\Schema;

use FastD\Database\Schema\Structure\Table;
use Iterator;

/**
 * Table Schema
 *
 * Class Schema
 *
 * @package FastD\Database\Schema
 */
abstract class Schema extends SchemaCache implements Iterator
{
    /**
     * @var array
     */
    protected $tables = [];

    /**
     * Schema constructor.
     * @param array $tables
     */
    public function __construct(array $tables = [])
    {
        $this->setTables($tables);

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->tables);
    }

    /**
     * @return SchemaReflex
     */
    public function getSchemaReflex()
    {
        return new SchemaReflex($this->getTables());
    }
    
    /**
     * @param Table[] $tables
     * @return $this
     */
    public function setTables(array $tables)
    {
        foreach ($tables as $table) {
            $this->tables[$table->getFullTableName()] = $table;
        }

        return $this;
    }

    /**
     * @param Table $table
     * @return $this
     */
    public function addTable(Table $table)
    {
        $this->tables[$table->getFullTableName()] = $table;
        
        $this->setCurrentTable($table);

        return $this;
    }

    /**
     * @param $name
     * @return Table|null
     */
    public function getTable($name)
    {
         return isset($this->tables[$name]) ? $this->tables[$name] : null;
    }

    /**
     * @return Table[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Table Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        $key = $this->key();

        $table = $this->tables[$key];
        
        $this->setCurrentTable($table);

        return $table;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->tables);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->tables);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->tables[$this->key()]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->tables);
    }
}