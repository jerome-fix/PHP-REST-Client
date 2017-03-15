<?php

namespace MRussell\REST\Endpoint\Interfaces;


interface ModelInterface extends EndpointInterface
{

    /**
     * Get or Set the Model ID Key used by the Model
     * @param null $id
     * @return mixed
     */
    public static function modelIdKey($id = NULL);

    /**
     * Set a particular Model property
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key,$value);

    /**
     * Get a specific piece of data on the Model
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * Retrieve a Model by ID using a GET Request
     * @param $id
     * @return mixed
     */
    public function retrieve($id = NULL);

    /**
     * Save the current Model
     * - Uses a POST if Model ID is not defined
     * - Uses a PUT request if Model ID is set
     * @return mixed
     */
    public function save();

    /**
     * Delete the current Model using a DELETE Request
     * @return mixed
     */
    public function delete();


}