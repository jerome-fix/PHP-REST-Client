<?php

namespace MRussell\REST\Endpoint\Data;


interface DataInterface extends \ArrayAccess
{
    /**
     * @param array $properties
     * @return self
     */
    public function setProperties(array $properties);

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @return array
     */
    public function asArray();

    /**
     * @return self
     */
    public function reset();

    /**
     * 
     * @return self
     */
    public function clear();

    /**
     * Set the Data array with passed in array
     * @param array $data
     * @return self
     */
    public function set(array $data);

    /**
     * Update and append to Data array
     * @param array $data
     * @return self
     */
    public function update(array $data);

}