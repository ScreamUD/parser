<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Exception\WriterException;
use Ddeboer\DataImport\Filter\ValidatorFilter;
use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Step\FilterStep;
use Ddeboer\DataImport\Workflow\StepAggregator;
use ParserBundle\Entity\Item;

class CsvParser extends Parser
{
    public function getReader($file)
    {
        $file = new \SplFileObject($file);
        $reader = new Reader\CsvReader($file);
        $reader->setHeaderRowNumber(0);

        return $reader;
    }

    public function getConverter()
    {
        return $this->converter
            ->map('[Product Code]', '[strProductCode]')
            ->map('[Product Name]', '[strProductName]')
            ->map('[Product Description]', '[strProductDesc]')
            ->map('[Stock]', '[intStock]')
            ->map('[Cost in GBP]', '[fltCost]')
            ->map('[Discontinued]', '[dtmDiscontinued]');
    }

    public function getWorkflow()
    {
        $workflow = new StepAggregator($this->getReader($this->file));

        $this->writer->disableTruncate();
        $workflow->setSkipItemOnFailure(true);
        $workflow->addStep($this->getConverter(), 100);

        $filterStep = new FilterStep();
        $filterStep->add($this->getCallbackUniqueFilter(), 100);
        $filterStep->add($this->getCallbackConditionsFilter(), 90);
        $filterStep->add($this->getValidationFilter(), 80);

        $workflow->addStep($filterStep, 90);

        if (!$this->testOption) {
            $workflow->addWriter($this->writer);
        }

        return $workflow;
    }

    public function getParseErrors()
    {
        // TODO: Implement getParseErrors() method.
    }

    public function process($file, $testOption)
    {
        $this->setTestOption($testOption);

        try {
            $file = new \SplFileObject($file);
            $this->getReader($file);
            $workflow = $this->getWorkflow();

            return $workflow;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * @return callable
     */
    protected function getCallbackFilters()
    {
        $callbackFilter = function ($data) {

            $result = true;
            foreach ($this->getFilter() as $func) {
                /** @var FilterResult $result */
                $result = $func($data);

                if ($result->getStatus() === false) {
                    $result = false;
                    break;
                }
            }

            if ($result) {
                $this->filter[$data['strProductCode']] = true;
            } else {
                $message = sprintf('Item cost less than 5 and product stock less than 10 - %s', $data['strProductCode']);
                throw new WriterException($message);
            }

            return $result;
        };

        return $callbackFilter;
    }
}