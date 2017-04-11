<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Database\Schema;

use FastD\Database\Drivers\DriverInterface;
use FastD\Database\Schema\Structure\Field;
use FastD\Database\Schema\Structure\Key;
use FastD\Database\Schema\Structure\Table;

/**
 * Class SchemaDriver
 * @package FastD\Database\Schema
 */
class SchemaParser extends Schema
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * SchemaParser constructor.
     *
     * @param DriverInterface $driverInterface
     * @param $onlyMatch
     */
    public function __construct(DriverInterface $driverInterface, $onlyMatch = false)
    {
        parent::__construct([]);

        $this->driver = $driverInterface;

        $this->reflexTableInDatabase($onlyMatch);
    }

    /**
     * @param $tables
     */
    protected function reflexTableSchema($tables)
    {
        $config = $this->driver->getConfig();
        $defaultPrefix = isset($config['database_prefix']) ? $config['database_prefix'] : '';
        $defaultSuffix = isset($config['database_suffix']) ? $config['database_suffix'] : '';

        foreach ($tables as $table) {
            $name = array_pop($table);

            if (!empty($defaultPrefix) && false !== ($index = strpos($name, $defaultPrefix))) {
                $len = strlen($defaultPrefix);
                $name = substr($name, $len);
                $prefix = $defaultPrefix;
                unset($len);
            } else {
                $prefix = '';
            }

            if (!empty($defaultSuffix) && false !== ($index = strpos($name, $defaultSuffix))) {
                $name = substr($name, 0, $index);
                $suffix = $defaultSuffix;
            } else {
                $suffix = '';
            }

            $table = new Table($name);
            $table->setPrefix($prefix);
            $table->setSuffix($suffix);

            if (!empty(($schemes = $this->parseTableSchema($table->getFullTableName())))) {
                // Parse fields
                foreach ($schemes as $scheme) {
                    $field = $this->parseTableSchemaFields($scheme);
                    $table->addField($field);
                    $this->setCacheField($field);
                }

                $this->addTable($table);
                // Save table cache.
                $this->setCurrentTable($table);
                $this->saveCache();
            }
        }
    }

    /**
     * @param string $table
     * @return array|bool
     */
    protected function parseTableSchema($table)
    {
        return $this->driver
            ->query(
                'SELECT
  TABLE_SCHEMA AS `db_name`,
  TABLE_NAME AS `table_name`,
  COLUMN_NAME AS `field`,
  COLUMN_DEFAULT AS `default`,
  IS_NULLABLE AS `nullable`,
  COLUMN_TYPE AS `type`,
  COLUMN_COMMENT AS `comment`,
  COLUMN_KEY AS `key`,
  EXTRA AS `extra`
FROM information_schema.COLUMNS
WHERE
  TABLE_NAME = \'' . $table . '\'
  AND TABLE_SCHEMA = \'' . $this->driver->getDbName() . '\';'
            )
            ->execute()
            ->getAll();
    }

    /**
     * @param $schema
     * @return Field
     */
    protected function parseTableSchemaFields($schema)
    {
        $type = $schema['type'];
        $pattern = '/(?<type>\w+)\(?(?<length>\d*)\)?\s?(?<unsigned>\w*)?/';
        preg_match($pattern, $type, $match);

        $field = new Field(
            $schema['field'],
            $match['type'],
            (int) $match['length'],
            $schema['nullable'] == 'NO' ? false : true,
            $schema['default'],
            $schema['comment']
        );

        $field->setUnsigned('unsigned' === trim($match['unsigned']) ? true : false);

        switch ($schema['extra']) {
            case 'auto_increment':
                $field->setIncrement();
                break;
        }

        switch ($schema['key']) {
            case 'PRI':
                $field->setKey(new Key(Key::PRIMARY));
                break;
            case 'MUL':
                $field->setKey(new Key(Key::INDEX));
                break;
            case 'UNI':
                $field->setKey(new Key(Key::UNIQUE));
                break;
        }

        return $field;
    }

    /**
     * @param $onlyMatch
     * @return void
     */
    protected function reflexTableInDatabase($onlyMatch = false)
    {
        $tables = $this->driver
            ->query('SHOW TABLES;')
            ->execute()
            ->getAll();

        if ($onlyMatch) {
            $prefix = $this->driver->getConfig()['database_prefix'];
            if (!empty($prefix)) {
                foreach ($tables as $key => $table) {
                    $table = array_values($table)[0];
                    if ($prefix !== substr($table, 0, strlen($prefix))) {
                        unset($tables[$key]);
                    }
                }
            }
        }

        $this->reflexTableSchema($tables);
    }
}