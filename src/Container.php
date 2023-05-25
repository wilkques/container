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
    protected static $aliases = array();

    /**
     * @param string|array $abstract
     * @param object|callable|\Closure|null $object
     * 
     * @return static
     */
    public function register($abstract, $object = null)
    {
        if (is_array($abstract)) {
            foreach ($abstract as $item) {
                call_user_func_array(array($this, 'register'), $item);
            }

            return $this;
        }

        if (is_string($object)) {
            $object = array($object);
        }

        if (is_callable($object)) {
            $object = $this->call($object);
        }

        if (is_array($object)) {
            $object = $this->resolve($abstract, $object);
        }

        return $this->registAbstract($abstract, $object);
    }

    /**
     * @param string $abstract
     * 
     * @return mixed
     */
    public function get($abstract)
    {
        if (!$this->hasAbstract($abstract)) {
            return $this->resolve($abstract);
        }

        return $this->resolveAbstract($abstract);
    }

    /**
     * @param string $abstract
     * @param callable|string|array $callable
     * 
     * @return static
     */
    public function bind($abstract, $callable = null)
    {
        if (is_string($callable)) {
            $callable = array($callable);
        }

        if (is_callable($callable)) {
            $callable = $this->call($callable);
        }

        if (is_array($callable)) {
            $callable = $this->resolve($abstract, $callable);
        }

        return $this->register($abstract, $callable);
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * 
     * @return mixed
     */
    public function make($abstract, $parameters = array())
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * @param string $abstract
     * @param array $parameters
     * 
     * @return mixed
     */
    protected function resolve($abstract, $parameters = array())
    {
        return $this->fireAbstract($abstract, "__construct", $parameters);
    }

    /**
     * @param string $method
     * 
     * @return bool
     */
    protected function isConstructMethod($method)
    {
        return $method === "__construct";
    }

    /**
     * @param string $abstract
     * 
     * @return bool
     */
    protected function hasAbstract($abstract)
    {
        return array_key_exists($abstract, $this->resolveAbstract());
    }

    /**
     * @param string $abstract
     * @param string $method
     * @param array $arguments
     * 
     * @return mixed
     */
    protected function invokeMethod($abstract, $method = "__construct", $arguments = array())
    {
        $resolvedAbstract = $this->get($abstract);

        if ($this->isConstructMethod($method)) {
            return $resolvedAbstract;
        }

        return call_user_func_array(array($resolvedAbstract, $method), $arguments);
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param string $method
     * @param array $arguments
     * 
     * @return array
     */
    protected function fireMethod($reflectionClass, $method = "__construct", $arguments = array())
    {
        if ($reflectionClass->hasMethod($method)) {
            $reflectionMethod = $reflectionClass->getMethod($method);

            $arguments = $this->fireArguments($reflectionMethod, $arguments);
        }

        return $arguments;
    }

    /**
     * @param bool $isCallConstructMethod
     * @param \ReflectionClass $reflectionClass
     * @param string $abstract
     * @param array $arguments
     * 
     * @return static
     */
    protected function fireConstruct($isCallConstructMethod, $reflectionClass, $abstract, $arguments = array())
    {
        $instanceArgs = array();

        if ($isCallConstructMethod)
            $instanceArgs = $arguments;

        $constructMethodName = "__construct";

        $arguments = $this->fireMethod($reflectionClass, $constructMethodName, $instanceArgs);

        if ($reflectionClass->hasMethod($constructMethodName))
            return $this->register($abstract, $reflectionClass->newInstanceArgs($arguments));

        return $this->register($abstract, new $abstract);
    }

    /**
     * @param string $abstract
     * @param string $method
     * @param array $arguments
     * 
     * @return mixed
     */
    protected function fireAbstract($abstract, $method = "__construct", $arguments = array())
    {
        if ($this->hasAbstract($abstract)) {
            return $this->invokeMethod($abstract, $method, $arguments);
        }

        $reflectionClass = new \ReflectionClass($abstract);

        $this->fireConstruct($this->isConstructMethod($method), $reflectionClass, $abstract, $arguments);

        $arguments = $this->fireMethod($reflectionClass, $method, $arguments);

        return $this->invokeMethod($abstract, $method, $arguments);
    }

    /**
     * @param \ReflectionMethod $reflection
     * @param array $arguments
     * 
     * @return array
     */
    protected function fireArguments($reflection, $arguments = array())
    {
        $reflectionParams = $reflection->getParameters();

        foreach ($reflectionParams as $key => $param) {
            $arg = $param->getName();

            // give value
            if (array_key_exists($arg, $arguments)) {
                $arguments[$key] = $arguments[$arg];

                unset($arguments[$arg]);

                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $arguments[$key] = $param->getDefaultValue();
            }

            if ($class = $param->getClass()) {
                $classNameForArguments = $class->getName();

                $arguments[$key] = $this->get($classNameForArguments);
            }

            if ($param->isArray()) {
                $arguments[$key] = array();
            }
        }

        return $arguments;
    }

    /**
     * @param \Closure $callable
     * @param array $arguments
     * 
     * @return array
     */
    protected function fireFunction($callable, array $arguments = array())
    {
        $reflectionFunction = new \ReflectionFunction($callable);

        return $this->fireArguments($reflectionFunction, $arguments);
    }

    /**
     * @param \Closure $callable
     * @param array $arguments
     * 
     * @return mixed
     */
    protected function fireClosure($callable, array $arguments = array())
    {
        return call_user_func_array($callable, $this->fireFunction($callable, $arguments));
    }

    /**
     * @param array|\Closure $callable
     * @param array $arguments
     * 
     * @return mixed
     */
    public function call($callable, array $arguments = array())
    {
        if ($callable instanceof \Closure) {
            return $this->fireClosure($callable, $arguments);
        }

        $abstract = array_shift($callable);

        is_object($abstract) && $abstract = get_class($abstract);

        $method = array_shift($callable);

        return $this->fireAbstract($abstract, $method,  $arguments);
    }

    /**
     * @param string $key
     * @param mixed $object
     * 
     * @return static
     */
    public function registAbstract($abstract, $object)
    {
        static::$aliases[$abstract] = $object;

        return $this;
    }

    /**
     * @param string|null $abstract
     * 
     * @return mixed
     */
    public function resolveAbstract($abstract = null)
    {
        if (!is_null($abstract) && $this->hasAbstract($abstract)) {
            return static::$aliases[$abstract];
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