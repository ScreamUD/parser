<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Workflow\StepAggregator;

/**
 * Interface ParserFactoryInterface
 * @package ParserBundle\Factory
 */
interface ParserFactoryInterface
{
    /**
     * get parser via file extension format
     *
     * @param string $file
     * @param string $format
     * @param bool $testOption
     * @return StepAggregator
     */
    public function getParser($file, $format, $testOption);

    /**
     * @return array
     */
    public function getParseErrors();
}