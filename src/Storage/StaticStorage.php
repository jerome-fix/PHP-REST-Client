<?php

namespace MRussell\REST\Storage;

class StaticStorage implements StorageControllerInterface {
    protected static $_STORAGE = array();

    protected $namespace = 'global';

    public function setNamespace(string $namespace): StorageControllerInterface {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Return the configured namespace
     * @return string
     */
    public function getNamespace(): string {
        return $this->namespace;
    }

    /**
     * @inheritdoc
     */
    public function get($key) {
        return static::getItem($this->namespace, $key);
    }

    /**
     * @inheritdoc
     */
    public function store($key, $value): bool {
        return static::setItem($this->namespace, $key, $value);
    }

    /**
     * @inheritdoc
     */
    public function remove($key): bool {
        return static::removeItem($this->namespace, $key);
    }

    /**
     * Get an Item from the Static Storage array
     * @param $namespace
     * @param $key
     * @return mixed|null
     */
    public static function getItem($namespace, $key) {
        if (isset(static::$_STORAGE[$namespace])) {
            if (isset(static::$_STORAGE[$namespace][$key])) {
                return static::$_STORAGE[$namespace][$key];
            }
        }
        return null;
    }

    /**
     * Set an Item in the Static Storage array for a particular Namespace
     * @param $namespace
     * @param $key
     * @return bool
     */
    public static function setItem($namespace, $key, $value): bool {
        if (!isset(static::$_STORAGE[$namespace])) {
            static::$_STORAGE[$namespace] = array();
        }
        static::$_STORAGE[$namespace][$key] = $value;
        return true;
    }

    /**
     * @param $namespace
     * @param $key
     * @return bool
     */
    public static function removeItem($namespace, $key): bool {
        if (isset(static::$_STORAGE[$namespace])) {
            if (isset(static::$_STORAGE[$namespace][$key])) {
                unset(static::$_STORAGE[$namespace][$key]);
            }
        }
        return true;
    }

    /**
     *
     * @param $namespace
     * @return bool
     */
    public static function clear($namespace): bool {
        if (isset(static::$_STORAGE[$namespace])) {
            unset(static::$_STORAGE[$namespace]);
        }
        return true;
    }
}
