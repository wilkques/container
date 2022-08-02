<?php

namespace Wilkques\Container;

/**
 * @see [wilkques](https://github.com/wilkques/container)
 * 
 * create by: wilkques
 */
class Container
{
    /** @var array */
    protected static $map = [];

    /**
     * @param string|array $className
     * @param object|callable|Closure|null $object
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
     * @param string $className
     * 
     * @return mixed
     */
    public static function get($className)
    {
        if (!class_exists($className, true)) {
            return null;
        }

        if (class_exists($className, true) && !array_key_exists($className, static::getMaps())) {
            return static::resolve($className);
        }

        return static::$map[$className];
    }

    /**
     * @param string $className
     * 
     * @return object
     */
    public static function resolve($className)
    {
        if (!class_exists($className, true)) {
            return null;
        }

        if (array_key_exists($className, static::getMaps())) {
            return static::$map[$className];
        }

        $reflectionClass = new \ReflectionClass($className);
        $reflectionConstructor = $reflectionClass->getConstructor();
        $reflectionParams = $reflectionConstructor->getParameters();

        $arguments = [];

        foreach ($reflectionParams as $param) {
            $classNameForArguments = $param->getClass()->getName();

            $arguments[] = static::get($classNameForArguments);
        }

        if (empty($arguments)) {
            return static::registerWithResolve(
                $classNameForArguments, 
                new $classNameForArguments()
            );
        }

        return static::registerWithResolve(
            $className, 
            $reflectionClass->newInstanceArgs($arguments)
        );
    }

    /**
     * @param string $className
     * @param object $class
     * 
     * @return object
     */
    public static function registerWithResolve($className, $class)
    {
        static::register(
            $className,
            $class
        );

        return static::get($className);
    }

    /**
     * @return array
     */
    public static function getMaps()
    {
        return static::$map;
    }
}
