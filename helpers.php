<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

/**
 * @param $name
 * @return string
 */
if (!function_exists('rename')) {
    function rename($name)
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
}


/**
 * @param $name
 * @return string
 */
function splitName($name)
{
    return preg_replace_callback(
        '([A-Z])',
        function ($matches) {
            return '_'.strtolower($matches[0]);
        },
        $name
    );
}