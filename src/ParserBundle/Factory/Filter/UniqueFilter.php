<?php

namespace ParserBundle\Factory\Filter;

use Ddeboer\DataImport\Exception\WriterException;

class UniqueFilter extends Filter
{
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
