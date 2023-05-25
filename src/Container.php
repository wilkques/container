<?php

namespace Wilkques\Container;

/**
 * @see [wilkques](https://github.com/wilkques/container)
 * 
 * create by: wilkques
 */
class Container
{
    /** 
     * @var array
     */
    protected static $aliases = array();

    /**
     * Register a abstract with the container.
     *
     * @param string|array $abstract
     * @param object|callable|\Closure|null $concrete
     * 
     * @return static
     */
    public function register($abstract, $concrete = null)
    {
        if (is_array($abstract)) {
            foreach ($abstract as $item) {
                $this->register($item);
            }

            return $this;
        }

        $concrete = $this->normalizeConcrete($concrete);

        $concrete = $this->resolveConcrete($abstract, $concrete);

        return $this->bindAbstract($abstract, $concrete);
    }

    /**
     * Resolve the given abstract using the concrete value.
     *
     * @param string $abstract
     * @param mixed $concrete
     * 
     * @return mixed
     */
    protected function resolveConcrete($abstract, $concrete)
    {
        if (is_callable($concrete)) {
            return $this->call($concrete);
        }

        if (is_array($concrete)) {
            return $this->resolve($abstract, $concrete);
        }

        return $concrete;
    }

    /**
     * Normalize the concrete value to an array.
     *
     * @param mixed $concrete
     * 
     * @return array
     */
    protected function normalizeConcrete($concrete)
    {
        if (is_string($concrete)) {
            return array($concrete);
        }

        return $concrete;
    }

    /**
     * Resolve the given abstract from the container.
     * 
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
     * Bind a new instance of a type into the container.
     * 
     * @param string $abstract
     * @param callable|string|array $concrete 
     * 
     * @return static
     */
    public function bind($abstract, $concrete  = null)
    {
        $concrete = $this->normalizeConcrete($concrete);

        $concrete = $this->resolveConcrete($abstract, $concrete);

        return $this->register($abstract, $concrete);
    }

    /**
     * Resolve the given abstract from the container.
     * 
     * @param string $abstract
     * @param array $arguments
     * 
     * @return mixed
     */
    public function make($abstract, $arguments = array())
    {
        return $this->resolve($abstract, $arguments);
    }

    /**
     * Resolve the given abstract from the container.
     * 
     * @param string $abstract
     * @param array $arguments
     * 
     * @return mixed
     */
    protected function resolve($abstract, $arguments = array())
    {
        return $this->fireAbstract($abstract, "__construct", $arguments);
    }

    /**
     * Check if the given method is the constructor.
     * 
     * @param string $method
     * 
     * @return bool
     */
    protected function isConstructMethod($method)
    {
        return $method === "__construct";
    }

    /**
     * Check if the container has a binding for the given abstract.
     * 
     * @param string $abstract
     * 
     * @return bool
     */
    protected function hasAbstract($abstract)
    {
        return array_key_exists($abstract, $this->resolveAbstract());
    }

    /**
     * Invoke the given method on the resolved instance.
     * 
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
     * Fire the method for the given reflection class.
     * 
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
     * Fire the constructor for the given abstract.
     * 
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
     * Fire the given abstract.
     * 
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

        if (!$this->isConstructMethod($method)) {
            $arguments = $this->fireMethod($reflectionClass, $method, $arguments);
        }

        return $this->invokeMethod($abstract, $method, $arguments);
    }

    /**
     * Resolve the constructor or method arguments.
     * 
     * @param \ReflectionMethod $reflectionMethod
     * @param array $arguments
     * 
     * @return array
     */
    protected function fireArguments($reflectionMethod, $arguments = array())
    {
        $resolvedArgs = array();

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $paramName = $parameter->getName();

            // give value
            if (array_key_exists($paramName, $arguments)) {
                $resolvedArgs[] = $arguments[$paramName];

                unset($arguments[$paramName]);
            } else if ($paramClass = $parameter->getClass()) {
                $resolvedArgs[] = $this->get($paramClass->getName());
            } else if ($parameter->isArray()) {
                $resolvedArgs[] = array();
            } else if ($parameter->isDefaultValueAvailable()) {
                $resolvedArgs[] = $parameter->getDefaultValue();
            }
        }

        return array_merge($resolvedArgs, array_values($arguments));
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
     * Invoke the given callable or closure with the given arguments.
     * 
     * @param \Closure $callable
     * @param array $arguments
     * 
     * @return mixed
     */
    protected function invokeCallable($callable, array $arguments = array())
    {
        return call_user_func_array($callable, $this->fireFunction($callable, $arguments));
    }

    /**
     * Call the given callable or closure.
     * 
     * @param array|\Closure $callable
     * @param array $arguments
     * 
     * @return mixed
     */
    public function call($callable, array $arguments = array())
    {
        if ($callable instanceof \Closure) {
            return $this->invokeCallable($callable, $arguments);
        }

        $abstract = array_shift($callable);

        if (is_object($abstract)) {
            $abstract = get_class($abstract);
        }

        $method = array_shift($callable);

        return $this->fireAbstract($abstract, $method,  $arguments);
    }

    /**
     * Bind a new instance of a type into the container.
     * 
     * @param string $key
     * @param mixed $object
     * 
     * @return static
     */
    public function bindAbstract($abstract, $object)
    {
        static::$aliases[$abstract] = $object;

        return $this;
    }

    /**
     * Get the resolved aliases.
     * 
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
     * Get the container instance.
     * 
     * @return static
     */
    public static function getInstance()
    {
        return new static;
    }
}
