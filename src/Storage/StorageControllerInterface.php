<?php

namespace MRussell\REST\Storage;

interface StorageControllerInterface {

    /**
     * Get a Key from the storage interface
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * Set a Key from the storage interface
     * @param $key
     * @param $value
     * @return boolean
     */
    public function store($key, $value): bool;

    /**
     * Remove a Key from the storage interface
     * @param $key
     * @return boolean
     */
    public function remove($key): bool;
}
