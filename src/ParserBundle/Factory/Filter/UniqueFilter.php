<?php

namespace ParserBundle\Factory\Filter;

use Ddeboer\DataImport\Exception\WriterException;

/**
 * Class UniqueFilter
 * @package ParserBundle\Factory\Filter
 */
class UniqueFilter extends Filter
{
    /**
     * checks for duplications
     *
     * @return \Closure
     */
    public function getCallable()
    {
        return function ($data) {
            $this->checkValue($data);

            if ($this->isExists($data)) {
                throw new WriterException(
                    sprintf('Duplication product code - %s', $this->getValue($data))
                );
            } else {
                $this->addValue($data);
            }

            return true;
        };
    }
}
