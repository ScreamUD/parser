<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Step\FilterStep;
use Ddeboer\DataImport\Step\MappingStep;
use Ddeboer\DataImport\Workflow\StepAggregator;
use ParserBundle\Entity\Item;

/**
 * Class CsvParser
 * @package ParserBundle\Factory
 */
class CsvParser extends Parser
{
    /**
     * @param string $file
     * @return Reader\CsvReader
     */
    public function getReader($file)
    {
        $file = new \SplFileObject($file);
        $reader = new Reader\CsvReader($file);
        $reader->setHeaderRowNumber(0);

        return $reader;
    }

    /**
     * @param MappingStep $converter
     * @return $this
     */
    public function setConverter(MappingStep $converter)
    {
        $this->converter = $converter;
        $this->converter->map('[Product Code]', '[strProductCode]')
            ->map('[Product Name]', '[strProductName]')
            ->map('[Product Description]', '[strProductDesc]')
            ->map('[Stock]', '[intStock]')
            ->map('[Cost in GBP]', '[fltCost]')
            ->map('[Discontinued]', '[dtmDiscontinued]');

        return $this;
    }

    /**
     * @param Reader $reader
     * @return StepAggregator
     */
    public function getWorkflow(Reader $reader)
    {
        $workflow = new StepAggregator($reader);

        $this->writer->disableTruncate();
        $workflow->setSkipItemOnFailure(true);

        $workflow->addStep($this->getConverter(), 100);
        $workflow->addStep($this->getFilterStep(), 90);

        if (!$this->testOption) {
            $workflow->addWriter($this->writer);
        }

        return $workflow;
    }

    /**
     * @return FilterStep
     */
    protected function getFilterStep()
    {
        $filterStep = new FilterStep();

        $filterStep->add((new Filter\UniqueFilter('strProductCode'))->getCallable(), 100);
        $filterStep->add(
            (new Filter\ConditionFilter('strProductCode', function ($data) {
                return $data['fltCost'] >= $this->getCost() || $data['intStock'] >= $this->getStock();
            }, sprintf('Item cost less than %s and product stock less than %s - [name]', $this->getCost(), $this->getStock())))                     ->getCallable(), 90);

        $filterStep->add(
            (new Filter\ValidatorFilter($this->validator, $this->constraintHelper, Item::class))
                ->getCallable(), 80
        );

        return $filterStep;
    }

    /**
     * {@inheritdoc}
     */
    public function process($file)
    {
        $reader = $this->getReader($file);
        $workflow = $this->getWorkflow($reader);

        return $this->getResult($workflow->process(), $reader);
    }
}
