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
     * @param string|array $abstract
     * @param object|callable|Closure|null $object
     * 
     * @return static|null
     */
    public function register($abstract, $object = null)
    {
        if (is_array($abstract)) {
            array_map(function ($item) {
                call_user_func_array(array(get_called_class(), 'register'), $item);
            }, $abstract);

            return $this;
        }

        if (is_callable($object)) {
            $object = $object();
        }

        return $this->setMaps($abstract, $object);
    }

    /**
     * @param string $abstract
     * 
     * @return mixed
     */
    public function get($abstract)
    {
        if (!array_key_exists($abstract, $this->getMaps())) {
            return $this->resolve($abstract);
        }

        return $this->getMaps($abstract);
    }

    /**
     * @param string $abstract
     * @param callable $closure
     * 
     * @return static
     */
    public function bind($abstract = null, $closure = null)
    {
        return $this->register($abstract, $closure);
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
        if (array_key_exists($abstract, $this->getMaps()) && $class = $this->getMaps($abstract)) {
            return $class;
        }

        $reflectionClass = new \ReflectionClass($abstract);
        $reflectionConstructor = $reflectionClass->getConstructor();
        $reflectionConstructor && $reflectionParams = $reflectionConstructor->getParameters();

        $arguments = [];

        if ($reflectionConstructor) {
            foreach ($reflectionParams as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $arguments[] = $param->getDefaultValue();
                }
                
                if ($class = $param->getClass()) {
                    $classNameForArguments = $class->getName();

                    $arguments[] = $this->get($classNameForArguments);
                }

                if ($param->isArray()) {
                    $arguments[] = [];
                }
            }
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
     * @param string $abstract
     * @param object $class
     * 
     * @return mixed
     */
    public function registerWithResolve($abstract, $class)
    {
        return $this->register(
            $abstract,
            $class
        )->get($abstract);
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
     * @return mixed
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
