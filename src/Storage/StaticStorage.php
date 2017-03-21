<?php

namespace MRussell\REST\Storage;

class StaticStorage implements StorageControllerInterface
{
    protected static $_STORAGE = array();

    protected $namespace = 'global';

    /**
     * @inheritdoc
     */
    public function get($key) {
        return static::getItem($this->namespace,$key);
    }

    /**
     * @inheritdoc
     */
    public function store($key, $value) {
        return static::setItem($this->namespace,$key,$value);
    }

    /**
     * @inheritdoc
     */
    public function remove($key){
        return static::removeItem($this->namespace,$key);
    }

    /**
     * Return the configure namespace
     * @return string
     */
    public function getNamespace(){
        return $this->namespace;
    }

    /**
     * Get an Item from the Static Storage array
     * @param $namespace
     * @param $key
     * @return mixed|null
     */
    public static function getItem($namespace,$key){
        if (isset(static::$_STORAGE[$namespace])){
            if (isset(static::$_STORAGE[$namespace][$key])){
                return static::$_STORAGE[$namespace][$key];
            }
        }
        return NULL;
    }

    /**
     * Set an Item in the Static Storage array for a particular Namespace
     * @param $namespace
     * @param $key
     * @return bool
     */
    public static function setItem($namespace,$key,$value){
        if (!isset(static::$_STORAGE[$namespace])){
            static::$_STORAGE[$namespace] = array();
        }
        static::$_STORAGE[$namespace][$key] = $value;
        return TRUE;
    }

    /**
     * @param $namespace
     * @param $key
     * @return bool
     */
    public static function removeItem($namespace,$key){
        if (isset(static::$_STORAGE[$namespace])){
            if (static::$_STORAGE[$namespace][$key]){
                unset(static::$_STORAGE[$namespace][$key]);
            }
        }
        return TRUE;
    }

    /**
     *
     * @param $namespace
     * @return bool
     */
    public static function clear($namespace){
        if (isset(static::$_STORAGE[$namespace])){
            unset(static::$_STORAGE[$namespace]);
        }
        return TRUE;
    }

}