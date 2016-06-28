<?php

namespace ParserBundle\Factory\Filter;

use ParserBundle\Exception\Exception;

/**
 * Abstract class Filter
 * @package ParserBundle\Factory\Filter
 */
abstract class Filter implements FilterInterface
{
    /**
     * @var array
     */
    protected $validValues = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * UniqueFilter constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function checkValue(array $data)
    {
        if (!isset($data[$this->name])) {
            throw new Exception('Data is invalid');
        }
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function getValue(array $data)
    {
        return $data[$this->name];
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function isExists(array $data)
    {
        return isset($this->validValues[$this->getValue($data)]);
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function addValue(array $data)
    {
        $this->validValues[$data[$this->name]] = true;

        return $this;
    }
}
