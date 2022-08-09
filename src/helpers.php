<?php

if (!function_exists('container')) {
    /**
     * @param string $abstruct
     * @param array $params
     */
    function container($abstruct = null, $params = null)
    {
        if (is_null($abstruct)) {
            return \Wilkques\Container\Container::getInstance();
        }
        
        return \Wilkques\Container\Container::getInstance()->make($abstruct, $params);
    }
}