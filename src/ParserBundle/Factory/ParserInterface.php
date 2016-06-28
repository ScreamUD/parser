<?php

namespace ParserBundle\Factory;

use ParserBundle\Parser\Result;

interface ParserInterface
{
    /**
     * @param string $file
     * @return Result
     */
    public function process($file);
}
