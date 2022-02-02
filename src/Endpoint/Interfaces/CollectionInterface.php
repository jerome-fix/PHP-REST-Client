<?php

namespace MRussell\REST\Endpoint\Interfaces;


interface CollectionInterface extends EndpointInterface {

    /**
     * Retrieve the Endpoint Collection
     * @return self
     */
    public function fetch(): self;

    /**
     * Set the Model Endpoint
     * @param mixed $model
     * @return self
     */
    public function setModelEndpoint($model): self;

    /**
     * Get a Model Endpoint based on Id
     * @param $id
     * @return ModelInterface|null
     */
    public function get($id);
}
