<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Reader;

interface ParserInterface
{
    public function getConverter();
    public function getWorkflow();
    public function getParseErrors();
    public function process();
    public function getReader($file);
    public function setFilter(array $filter);
    public function getFilter();
}