<?php

namespace ParserBundle\Helper;

use ParserBundle\Model\ModelInterface;

/**
 * Interface ConstraintInterface
 * @package ParserBundle\Helper
 */
interface ConstraintInterface
{
    /**
     * gets constraints from entity asserts
     *
     * @param ModelInterface $entity
     * @return array
     */
    public function getConstraint(ModelInterface $entity);
}