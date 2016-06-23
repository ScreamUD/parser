<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Workflow;

interface ParserFactoryInterface
{
    /**
     * get parser via file extension format
     *
     * @param string $file
     * @param string $format
     * @return Workflow
     * @throws \Exception
     */
    public function getParser($file, $format);
}