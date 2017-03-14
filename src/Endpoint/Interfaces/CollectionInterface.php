<?php

namespace MRussell\REST\Endpoint\Interfaces;


interface CollectionInterface {

    /**
     * Retrieve the Endpoint Collection
     * @return mixed
     */
    public function fetch();

    /**
     * Get the current collection
     * @return mixed
     */
    public function getCollection();

    /**
     * Set the Model Endpoint
     * @param mixed $model
     * @return mixed
     */
    public function setModelEndpoint($model);

    /**
     * Get a Model Endpoint based on Id
     * @param $id
     * @return mixed
     */
    public function get($id);
}