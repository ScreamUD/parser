<?php

namespace ParserBundle\Helper;

use ParserBundle\Model\ModelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ConstraintHelper
 * @package ParserBundle\Helper
 */
class ConstraintHelper implements ConstraintInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * ConstraintHelper constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * data for correct validation from entity asserts
     *
     * @param ModelInterface $entity
     * @return array
     */
    public function getConstraint(ModelInterface $entity)
    {
        $metadata = $this->validator->getMetadataFor($entity);
        $constrainedProperties = $metadata->getConstrainedProperties();

        $result = [];

        foreach($constrainedProperties as $constrainedProperty) {

            $propertyMetadata=$metadata->getPropertyMetadata($constrainedProperty);
            $constraints=$propertyMetadata[0]->constraints;
            foreach($constraints as $constraint)
            {
                $result[] = ['field' => $constrainedProperty, 'constraint' => $constraint];
            }
        }

        return $result;
    }
}