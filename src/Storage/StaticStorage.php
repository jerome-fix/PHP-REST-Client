<?php

namespace MRussell\REST\Storage;

class StaticStorage implements StorageControllerInterface
{
    protected static $_STORAGE = array();

    protected $namespace = 'static';

    public function get($key) {
        return static::getItem($this->namespace,$key);
    }

    public function set($key, $value) {
        if (!static::setItem($this->namespace,$key,$value)){
            return FALSE;
        }
        return $this;
    }

    public static function getItem($namespace,$key){
        if (isset(static::$_STORAGE[$namespace])){
            if (isset(static::$_STORAGE[$namespace][$key])){
                return static::$_STORAGE[$namespace][$key];
            }
        }
        return NULL;
    }

    public static function setItem($namespace,$key,$value){
        if (!isset(static::$_STORAGE[$namespace])){
            static::$_STORAGE[$namespace] = array();
        }
        static::$_STORAGE[$namespace][$key] = $value;
        return TRUE;
    }

}