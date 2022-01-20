<?php

namespace MRussell\REST\Endpoint\Data;


interface DataInterface extends \ArrayAccess
{
    /**
     * Set the properties that control the data
     * @param array $properties
     * @return self
     */
    public function setProperties(array $properties): void;

    /**
     * Get the properties configured on the Data
     * @return array
     */
    public function getProperties(): array;

    /**
     * Return the data as an array
     * @return array
     */
    public function toArray(): array;

    /**
     * Reset the DataInterface back to defaults
     * @return self
     */
    public function reset(): self;

    /**
     * Clear out the Data
     * @return self
     */
    public function clear(): self;

    /**
     * Update and append to Data array
     * @param array $data
     * @return self
     */
    public function update(array $data): self;

}