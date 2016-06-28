<?php

namespace ParserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ItemRepository
 * @package ParserBundle\Repository
 */
class ItemRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function clearTable()
    {
        return $this
            ->getEntityManager()
            ->createQuery(
                'DELETE ParserBundle:Item p'
            )
            ->getResult();
    }
}