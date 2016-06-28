<?php

namespace ParserBundle\Factory\Filter;

use \Ddeboer\DataImport\Filter\ValidatorFilter as DdeboerValidatorFilter;
use ParserBundle\Helper\ConstraintInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorFilter implements FilterInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var string
     */
    protected $entityClassName;
    /**
     * @var ConstraintInterface
     */
    protected $constraintHelper;

    /**
     * ValidatorFilter constructor.
     * @param ValidatorInterface $validator
     * @param ConstraintInterface $constraintHelper
     * @param string $entityClassName
     */
    public function __construct($validator, $constraintHelper, $entityClassName)
    {
        $this->validator = $validator;
        $this->constraintHelper = $constraintHelper;
        $this->entityClassName = $entityClassName;
    }

    public function getCallable()
    {
        $validatorFilter = new DdeboerValidatorFilter($this->validator);

        $arrayOfConstraints = $this->constraintHelper->getConstraint(new $this->entityClassName());
        foreach ($arrayOfConstraints as $value) {
            $validatorFilter->add($value['field'], $value['constraint']);
        }

        $validatorFilter->throwExceptions();
        $validatorFilter->setStrict(false);

        return $validatorFilter;
    }
}
