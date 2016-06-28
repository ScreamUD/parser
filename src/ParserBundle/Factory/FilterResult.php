<?php

namespace ParserBundle\Factory;

class FilterResult
{
    protected $message;
    protected $status;

    /**
     * FilterResult constructor.
     * @param $message
     * @param $status
     */
    public function __construct($message, $status)
    {
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}