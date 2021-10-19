<?php

namespace Wilkques\Container;

class Container
{
    protected static $map = [];

    /**
     * @param string $className
     * @param object|callable|Closure $object
     */
    public static function register($className, $object = null)
    {
        if (is_array($className)) {
            array_map(function ($item) {
                call_user_func_array(array(get_called_class(), 'register'), $item);
            }, $className);

            return;
        }

        if (is_callable($object)) {
            $object = $object();
        }

        static::$map[$className] = $object;
    }

    /**
     * @return mixed
     */
    public static function get($className)
    {
        return static::$map[$className];
    }

    /**
     * @return array
     */
    public static function getMaps()
    {
        return static::$map;
    }
}
