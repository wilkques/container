<?php

if (!function_exists('app_container')) {
    /**
     * @param string $abstruct
     * @param array $params
     */
    function app_container($abstruct = null, $params = null)
    {
        if (is_null($abstruct)) {
            return \Wilkques\Container\Container::getInstance();
        }
        
        return \Wilkques\Container\Container::getInstance()->make($abstruct, $params);
    }
}