<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Step\MappingStep;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use ParserBundle\Helper\ConstraintHelper;
use ParserBundle\Helper\ConstraintInterface;
use ParserBundle\Parser\Result;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ddeboer\DataImport\Result as DdeboerResult;

abstract class Parser implements ParserInterface
{
    /**
     * @var DoctrineWriter
     */
    protected $writer;
    /**
     * @var bool
     */
    protected $testOption;
    /**
     * @var MappingStep
     */
    protected $converter;
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var ConstraintHelper
     */
    protected $constraintHelper;
    /**
     * @var int
     */
    protected $cost;
    /**
     * @var int
     */
    protected $stock;

    /**
     * @param DoctrineWriter $writer
     */
    public function setWriter(DoctrineWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param boolean $testOption
     */
    public function setTestOption($testOption)
    {
        $this->testOption = $testOption;
    }

    /**
     * @return MappingStep
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * @param MappingStep $converter
     * @return $this
     */
    public function setConverter(MappingStep $converter)
    {
        $this->converter = $converter;

        return $this;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @param ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return ConstraintHelper
     */
    public function getConstraintHelper()
    {
        return $this->constraintHelper;
    }

    /**
     * @param ConstraintInterface $constraintHelper
     */
    public function setConstraintHelper(ConstraintInterface $constraintHelper)
    {
        $this->constraintHelper = $constraintHelper;
    }

    /**
     * @return int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param int $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @param 
     * @return array
     */
    protected function getParseErrors($reader)
    {
        $result = [];

        if (method_exists($reader, 'hasErrors') && $reader->hasErrors()) {
            $result = array_keys($reader->getErrors());
        }

        return $result;
    }

    /**
     * @param DdeboerResult $parseResult
     * @return Result
     */
    public function getResult($parseResult, $reader)
    {
        $result = new Result();
        $result->setEndTime($parseResult->getEndTime()->format('Y-m-d h:m:s'));
        $result->setExceptions($parseResult->getExceptions());
        $result->setErrors($this->getParseErrors($reader));
        $result->setSuccessCount($parseResult->getSuccessCount());

        return $result;
    }

    /**
     * @param Reader $reader
     * @return mixed
     */
    abstract protected function getWorkflow(Reader $reader);
    abstract protected function getReader($file);
}
