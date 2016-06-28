<?php

namespace ParserBundle\Factory\Filter;

interface FilterInterface
{
    /**
     * @return \Closure|Object
     */
    public function getCallable();
}
