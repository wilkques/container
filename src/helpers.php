<?php

if (!function_exists('container')) {
    /**
     * @param string $abstruct
     * @param array $params
     * 
     * @return mixed|\Wilkques\Container\Container
     */
    function container($abstruct = null, $params = array())
    {
        if (is_null($abstruct)) {
            return \Wilkques\Container\Container::getInstance();
        }
        
        return \Wilkques\Container\Container::getInstance()->make($abstruct, $params);
    }
}