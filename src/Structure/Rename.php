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
 * 重名模块
 *
 * trait Rename
 * @package Database\Schema\Structure
 */
trait Rename
{
    /**
     * @param $name
     * @return string
     */
    protected function rename($name)
    {
        if (strpos($name, '_')) {
            $arr = explode('_', $name);
            $name = array_shift($arr);
            foreach ($arr as $value) {
                $name .= ucfirst($value);
            }
        }

        return $name;
    }

    /**
     * @param $name
     * @return string
     */
    protected function splitName($name)
    {
        return preg_replace_callback('([A-Z])', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $name);
    }
}