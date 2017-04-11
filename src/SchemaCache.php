<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Database\Schema;

use FastD\Database\Cache\CacheInterface;
use FastD\Database\Schema\Structure\Table;
use FastD\Database\Schema\Structure\Field;

/**
 * Class SchemaCache
 * @package FastD\Database\Schema
 */
class SchemaCache implements CacheInterface
{
    /**
     * Current cache.
     *
     * @var Field[]
     */
    protected $fieldsCache = [];

    /**
     * @var string
     */
    protected $fieldsCacheFile;

    /**
     * @var string
     */
    protected $fieldsCacheDir = __DIR__ . '/fieldsCache';

    /**
     * @var Table
     */
    protected $table;

    /**
     * SchemaCache constructor.
     */
    public function __construct()
    {
        if (!file_exists($this->fieldsCacheDir)) {
            mkdir($this->fieldsCacheDir, 0755, true);
        }
    }

    /**
     * Set current table and current cache file and cache fields.
     *
     * @param Table $table
     * @return $this
     */
    public function setCurrentTable(Table $table)
    {
        $this->fieldsCacheFile = $this->fieldsCacheDir . DIRECTORY_SEPARATOR . '.table.' . $table->getFullTableName() . '.cache';

        if (file_exists($this->fieldsCacheFile)) {
            try {
                $this->fieldsCache = include $this->fieldsCacheFile;
                $this->fieldsCache = unserialize($this->fieldsCache);
            } catch (\Exception $e) {
                $this->fieldsCache = [];
            }
        }else if(!is_null($this->table) && $this->table->getTableName() !== $table->getTableName()) {
            $this->fieldsCache = [];
        }

        $this->table = $table;

        return $this;
    }

    /**
     * @return Table
     */
    public function getCurrentTable()
    {
        return $this->table;
    }

    /**
     * @return bool
     */
    public function hasCacheField()
    {
        return file_exists($this->fieldsCacheFile);
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function setCacheField(Field $field)
    {
        $this->fieldsCache[$field->getName()] = $field;

        return $this;
    }

    /**
     * @param $name
     */
    public function unsetCacheField($name)
    {
        if (isset($this->fieldsCache[$name])) {
            unset($this->fieldsCache[$name]);
        }
    }

    /**
     * @return string
     */
    public function getCacheFile()
    {
        return $this->fieldsCacheFile;
    }

    /**
     * @return int
     */
    public function saveCache()
    {
        return file_put_contents($this->fieldsCacheFile, '<?php return ' . var_export(serialize($this->fieldsCache), true) . ';');
    }

    /**
     * @return array|Field[]
     */
    public function getCache()
    {
        return $this->fieldsCache;
    }

    /**
     * @return void
     */
    public function clearCache()
    {
        if (file_exists($this->fieldsCacheFile)) {
            unlink($this->fieldsCacheFile);
        }
    }
}