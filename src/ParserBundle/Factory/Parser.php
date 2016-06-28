<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Filter\ValidatorFilter;
use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Step\MappingStep;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use ParserBundle\Helper\ConstraintHelper;
use ParserBundle\Model\ModelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var Reader
     */
    protected $reader;
    /**
     * @var MappingStep
     */
    protected $converter;
    /**
     * @var string
     */
    protected $file;
    /**
     * @var array
     */
    protected $filter;
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var ConstraintHelper
     */
    protected $constraintHelper;


    /**
     * @return DoctrineWriter
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * @param DoctrineWriter $writer
     */
    public function setWriter($writer)
    {
        $this->writer = $writer;
    }

    /**
     * @return boolean
     */
    public function isTestOption()
    {
        return $this->testOption;
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
     */
    public function setConverter($converter)
    {
        $this->converter = $converter;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter)
    {
        $this->filter = $filter;
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
    public function setValidator($validator)
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
     * @param ConstraintHelper $constraintHelper
     */
    public function setConstraintHelper($constraintHelper)
    {
        $this->constraintHelper = $constraintHelper;
    }

    /**
     * @param ModelInterface $item
     * @return ValidatorFilter
     */
    protected function getValidationFilter(ModelInterface $item)
    {
        $validatorFilter = new ValidatorFilter($this->getValidator());

        $arrayOfConstraints = $this->getConstraintHelper()->getConstraint($item);
        foreach ($arrayOfConstraints as $value) {
            $validatorFilter->add($value['field'], $value['constraint']);
        }

        $validatorFilter->throwExceptions();
        $validatorFilter->setStrict(false);

        return $validatorFilter;
    }
}
