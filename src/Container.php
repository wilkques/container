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
    protected static $aliases = [];

    /**
     * @param string|array $className
     * @param object|callable|Closure|null $object
     * 
     * @return static|null
     */
    public function register($className, $object = null)
    {
        if (is_array($className)) {
            array_map(function ($item) {
                call_user_func_array(array(get_called_class(), 'register'), $item);
            }, $className);

            return $this;
        }

        if (is_callable($object)) {
            $object = $object();
        }

        return $this->setMaps($className, $object);
    }

    /**
     * @param string $className
     * 
     * @return mixed
     */
    public function get($className)
    {
        if (class_exists($className, true) && !array_key_exists($className, $this->getMaps())) {
            return $this->resolve($className);
        }

        return $this->getMaps($className);
    }

    /**
     * @param string|null $abstract
     * @param array $parameters
     * 
     * @return mixed
     */
    public function make($abstract = null, $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * 
     * @return mixed
     */
    protected function resolve($abstract = null, $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (empty($parameters)) {
            return $this->fireAbstract($abstract);
        }

        return $this->registerWithResolve(
            $abstract,
            new $abstract(...$this->fireParameters($parameters))
        );
    }

    /**
     * @param string $abstract
     * 
     * @return mixed
     */
    protected function fireAbstract($abstract)
    {
        $reflectionClass = new \ReflectionClass($abstract);
        $reflectionConstructor = $reflectionClass->getConstructor();
        $reflectionParams = $reflectionConstructor->getParameters();

        $arguments = [];

        foreach ($reflectionParams as $param) {
            $classNameForArguments = $param->getClass()->getName();

            $arguments[] = $this->get($classNameForArguments);
        }

        return $this->registerWithResolve(
            $abstract,
            $reflectionClass->newInstanceArgs($arguments)
        );
    }

    /**
     * @param array $parameters
     * 
     * @return array
     */
    protected function fireParameters($parameters)
    {
        return array_map(function ($parameter) {
            if (array_key_exists($parameter, $this->getMaps())) {
                return $this->get($parameter);
            }

            if (class_exists($parameter)) {
                return $this->get($parameter);
            }

            return $parameter;
        }, $parameters);
    }

    /**
     * @param string $className
     * @param object $class
     * 
     * @return mixed
     */
    public function registerWithResolve($className, $class)
    {
        return $this->register(
            $className,
            $class
        )->get($className);
    }

    /**
     * Get the alias for an abstract if available.
     *
     * @param  string  $abstract
     * @return string
     */
    public function getAlias($abstract)
    {
        return isset(static::$aliases[$abstract])
            ? $this->getAlias($this->getMaps($abstract))
            : $abstract;
    }

    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return static
     */
    public function setMaps($key, $value)
    {
        static::$aliases[$key] = $value;

        return $this;
    }

    /**
     * @param string|null $key
     * 
     * @return array
     */
    public function getMaps($key = null)
    {
        if (!is_null($key)) {
            return static::$aliases[$key];
        }

        return static::$aliases;
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        return new static;
    }
}
