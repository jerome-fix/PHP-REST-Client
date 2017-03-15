<?php

namespace MRussell\REST\Storage;

interface StorageControllerInterface {

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @param $value
     * @return self|FALSE
     */
    public function set($key,$value);

}