<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace FastD\Migration;

use PDO;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Migration
 * @package FastD\Migration
 */
class Migration
{
    /**
     * @var string
     */
    protected $cachePath = __DIR__.'/.cache/tables';

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var Table[]
     */
    protected $tables = [];

    /**
     * Migration constructor.
     * @param PDO|null $pdo
     */
    public function __construct(PDO $pdo = null)
    {
        if (null === $pdo) {
            $file = getcwd().'/migrate.yml';
            if ( ! file_exists($file)) {
                throw new \RuntimeException('cannot such config file '.$file);
            }
            $config = Yaml::parse(file_get_contents($file));
            unset($file);
            $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s', $config['host'], $config['dbname']), $config['user'], $config['pass']);
        }

        if ( ! file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }

        $this->pdo = $pdo;
    }

    /**
     * @param Table $table
     * @return bool|mixed
     */
    final protected function getCache(Table $table)
    {
        if (!$this->hasCache($table)) {
            return false;
        }
        $cache = file_get_contents($this->cachePath.'/'.$table->getTableName());

        return unserialize($cache);
    }

    /**
     * @param Table $table
     * @return bool|int
     */
    final protected function saveCache(Table $table)
    {
        $cacheFile = $this->cachePath.'/'.$table->getTableName();

        return file_put_contents($cacheFile, serialize($table->getColumns()));
    }

    /**
     * @param Table $table
     * @return bool
     */
    final protected function hasCache(Table $table)
    {
        return file_exists($this->cachePath.'/'.$table->getTableName());
    }

    /**
     * @param Table $table
     */
    final protected function clearCache(Table $table)
    {
        $cacheFile = $this->cachePath.'/'.$table->getTableName();

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * @return array
     */
    public function extract()
    {
        $tables = $this->pdo
            ->query('SHOW TABLES;')
            ->fetchAll();

        $result = [];
        foreach ($tables as $key => $table) {
            $result[$table] = $this->parseTable(array_values($table)[0]);
        }

        return $result;
    }

    /**
     * @param $name
     * @return Table
     */
    protected function parseTable($name)
    {
        $table = new Table($name);

        if ( ! empty(($columns = $this->showTableSchema($table->getTableName())))) {
            foreach ($columns as $column) {
                $column = $this->parseColumn($column);
                $table->addColumn($column);
            }
        }

        return $table;
    }

    /**
     * @param $table
     * @return array
     */
    protected function showTableSchema($table)
    {
        return $this->pdo
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
  TABLE_NAME = \''.$table.'\';'
            )
            ->fetchAll();
    }

    /**
     * @param $schema
     * @return Column
     */
    protected function parseColumn($schema)
    {
        $type = $schema['type'];
        $pattern = '/(?<type>\w+)\(?(?<length>\d*)\)?\s?(?<unsigned>\w*)?/';
        preg_match($pattern, $type, $match);

        $column = new Column(
            $schema['field'],
            $match['type'],
            (int)$match['length'],
            $schema['nullable'] == 'NO' ? false : true,
            $schema['default'],
            $schema['comment']
        );

        $column->setUnsigned('unsigned' === trim($match['unsigned']) ? true : false);

        switch ($schema['extra']) {
            case 'auto_increment':
                $column->setIncrement();
                break;
        }

        switch ($schema['key']) {
            case 'PRI':
                $column->setKey(new Index(Index::PRIMARY));
                break;
            case 'MUL':
                $column->setKey(new Index(Index::INDEX));
                break;
            case 'UNI':
                $column->setKey(new Index(Index::UNIQUE));
                break;
        }

        return $column;
    }

    /**
     * @param Table $table
     * @param bool $force
     * @return string
     */
    public function update(Table $table, $force = false)
    {
        return ! $this->hasCache($table) ? $this->create($table, $force) : $this->alter($table);
    }

    /**
     * @param Table $table
     * @param bool $force
     * @return string
     */
    public function create(Table $table, $force = false)
    {
        $columns = [];
        $keys = [];

        foreach ($table->getColumns() as $name => $column) {
            $columns[] = preg_replace(
                '/\s{2,}/',
                ' ',
                implode(
                    ' ',
                    [
                        '`'.$column->getName().'`',
                        $column->getType().(! empty($column->getLength()) ? '('.$column->getLength().')' : ''),
                        ($column->isUnsigned()) ? 'UNSIGNED' : '',
                        ($column->isNullable() ? '' : ('NOT NULL')),
                        (! $column->isIncrement() && ! $column->isUnique() && ! $column->isPrimary(
                        ) ? 'DEFAULT "'.$column->getDefault().'"' : ''),
                        ($column->isIncrement()) ? 'AUTO_INCREMENT' : '',
                        'COMMENT "'.$column->getComment().'"',
                    ]
                )
            );

            if (null !== $column->getKey()) {
                if ($column->isPrimary()) {
                    $keys[] = 'PRIMARY KEY (`'.$column->getName().'`)';
                } else {
                    if ($column->isUnique()) {
                        $keys[] = 'UNIQUE KEY `unique_'.$column->getName().'` (`'.$column->getName().'`)';
                    } else {
                        if ($column->isIndex()) {
                            $keys[] = 'KEY `index_'.$column->getName().'` (`'.$column->getName().'`)';
                        }
                    }
                }
            }
        }

        $schema = $force ? ('DROP TABLE IF EXISTS `'.$table->getTableName().'`;'.PHP_EOL.PHP_EOL) : '';
        $schema .= 'CREATE TABLE `'.$table->getTableName().'` IF NOT EXISTS `'.$table->getTableName().'` (';
        $schema .= PHP_EOL.implode(','.PHP_EOL, $columns).(empty($keys) ? PHP_EOL : (','.PHP_EOL.implode(
                    ','.PHP_EOL,
                    $keys
                ).PHP_EOL));
        $schema .= ') ENGINE '.$table->getEngine().' CHARSET '.$table->getCharset().' COMMENT "'.$table->getComment(
            ).'";';

        $this->saveCache($table);

        return $schema;
    }

    /**
     * @param Table $table
     * @return string
     */
    public function alter(Table $table)
    {
        $cache = $this->getCache($table);

        $add = [];
        $change = [];
        $drop = [];
        $keys = [];

        foreach ($table->getColumns() as $name => $column) {
            if (array_key_exists($column->getName(), $cache)) {
                continue;
            }
            $add[] = str_replace(
                '  ',
                ' ',
                implode(
                    ' ',
                    [
                        'ALTER TABLE `'.$table->getTableName().'` ADD `'.$column->getName().'`',
                        $column->getType().(! empty($column->getLength()) ? '('.$column->getLength().')' : ''),
                        ($column->isUnsigned()) ? 'UNSIGNED' : '',
                        ($column->isNullable() ? '' : ('NOT NULL')),
                        (( ! empty($column->getDefault()) && ! $column->isIncrement() && ! $column->isUnique(
                            ) && ! $column->isPrimary()) ? 'DEFAULT "'.$column->getDefault().'"' : ''),
                        ($column->isPrimary()) ? 'AUTO_INCREMENT' : '',
                        'COMMENT "'.$column->getComment().'";',
                    ]
                )
            );
            if (null !== $column->getKey()) {
                $keys[] = implode(
                    ' ',
                    [
                        'ALTER TABLE `'.$table->getTableName().'` ADD '.($column->getKey()->isPrimary(
                        ) ? 'PRIMARY KEY' : $column->getKey()->getKey()),
                        '`index_'.$column->getName().'` ('.$column->getName().');',
                    ]
                );
            }
        }

        // Alter table change column.
        foreach ($table->getAlterColumns() as $name => $field) {
            if (array_key_exists($name, $cache)) {
                if ( ! $cache[$name]->equal($field)) {
                    $change[] = implode(
                        ' ',
                        [
                            'ALTER TABLE `'.$table->getTableName().'` CHANGE `'.$name.'` `'.$field->getName().'`',
                            $field->getType().'('.$field->getLength().')',
                            ($field->isUnsigned()) ? 'UNSIGNED' : '',
                            ($field->isNullable() ? '' : ('NOT NULL')),
                            (( ! empty($field->getDefault()) && ! $field->isIncrement() && ! $field->isUnique(
                                ) && ! $field->isPrimary()) ? 'DEFAULT "'.$field->getDefault().'"' : ''),
                            ($field->isPrimary()) ? 'AUTO_INCREMENT' : '',
                            'COMMENT "'.$field->getComment().'";',
                        ]
                    );
                    if (null !== $field->getKey()) {
                        $keys[] = implode(
                            ' ',
                            [
                                'ALTER TABLE `'.$table->getTableName().'` ADD '.($field->getKey()->isPrimary(
                                ) ? 'PRIMARY KEY' : $field->getKey()->getKey()),
                                '`index_'.$field->getName().'` ('.$field->getName().');',
                            ]
                        );
                    }
                }
            }
        }

        // Alter table drop column and drop map key.
        foreach ($table->getDropColumns() as $name => $field) {
            if ( ! array_key_exists($name, $cache)) {
                continue;
            }
            $drop[] = implode(
                ' ',
                [
                    'ALTER TABLE `'.$table->getTableName().'`',
                    'DROP `'.$field.'`;',
                ]
            );
        }

        $this->saveCache($table);

        return implode(
            PHP_EOL,
            array_filter(
                [
                    implode(PHP_EOL, $add),
                    implode(PHP_EOL, $change),
                    implode(PHP_EOL, $drop),
                    implode(PHP_EOL, $keys),
                ]
            )
        );
    }

    /**
     * @param Table $table
     * @param bool $force
     * @return string
     */
    public function drop(Table $table, $force = false)
    {
        $this->clearCache($table);

        return 'DROP TABLE '.($force ? 'IF EXISTS ' : '').'`'.$table->getTableName().'`;';
    }
}