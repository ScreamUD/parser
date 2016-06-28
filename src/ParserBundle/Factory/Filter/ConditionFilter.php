<?php

namespace ParserBundle\Factory\Filter;

use Ddeboer\DataImport\Exception\WriterException;

class ConditionFilter extends Filter
{
    /**
     * @var \Closure
     */
    protected $conditionCallback;
    /**
     * @var string
     */
    protected $message;

    /**
     * ConditionFilter constructor.
     * @param string $name
     * @param \Closure $conditionCallback
     * @param string $message
     */
    public function __construct($name, \Closure $conditionCallback, $message)
    {
        parent::__construct($name);
        $this->conditionCallback = $conditionCallback;
        $this->message = $message;
    }

    /**
     * @return \Closure
     */
    public function getCallable()
    {
        return function ($data) {
            $this->checkValue($data);

            if (!$this->isValid($data)) {
                throw new WriterException(
                    str_replace('[name]', $this->getValue($data), $this->message)
                );
            } else {
                $this->addValue($data);
            }

            return true;
        };
    }

    public function isValid($data)
    {
        $callback = $this->conditionCallback;

        return $callback($data);
    }
}
