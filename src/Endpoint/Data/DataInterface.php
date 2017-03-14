<?php

namespace MRussell\REST\Endpoint\Data;


interface DataInterface
{
    /**
     * @param array $properties
     * @return $this
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
     * @return $this
     */
    public function reset();

    /**
     * @return $this
     */
    public function clear();

    /**
     * Set the Data array with passed in array
     * @param array $data
     * @return $this
     */
    public function set(array $data);

    /**
     * Update and append to Data array
     * @param array $data
     * @return $this
     */
    public function update(array $data);

}