<?php

namespace ParserBundle\Factory\Filter;

/**
 * Interface FilterInterface
 * @package ParserBundle\Factory\Filter
 */
interface FilterInterface
{
    /**
     * @return \Closure
     */
    public function getCallable();
}
