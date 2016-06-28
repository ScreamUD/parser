<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Filter\ValidatorFilter;
use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Step\FilterStep;
use Ddeboer\DataImport\Step\MappingStep;
use Ddeboer\DataImport\Workflow\StepAggregator;
use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use ParserBundle\Entity\Item;
use ParserBundle\Helper\ConstraintInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use Ddeboer\DataImport\Exception\WriterException;

/**
 * Class ParserFactory
 * @package ParserBundle\Factory
 */
class ParserFactory implements ParserFactoryInterface
{
    const ITEM_MIN_COST = 5;
    const ITEM_MIN_STOCK = 10;

    /**
     * filter for unique data
     *
     * @var array
     */
    protected $filter = [];

    /**
     * @var DoctrineWriter
     */
    protected $writer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var MappingStep
     */
    protected $converter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var \Doctrine\Common\EventManager
     */
    protected $evm;

    protected $helper;

    /**
     * ParserFactory constructor.
     * @param DoctrineWriter $writer
     * @param ValidatorInterface $validator
     * @param MappingStep $converter
     * @param LoggerInterface $logger
     * @param EntityManager $em
     * @param ConstraintInterface $helper
     */
    public function __construct(
        DoctrineWriter $writer,
        ValidatorInterface $validator,
        MappingStep $converter,
        LoggerInterface $logger,
        EntityManager $em,
        ConstraintInterface $helper
    )
    {
        $this->writer = $writer;
        $this->validator = $validator;
        $this->converter = $converter;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->em = $em;
        $this->evm = $em->getEventManager();
    }

    /**
     * get parser via file extension format
     *
     * @param string $file
     * @param string $format
     * @param bool $testOption
     * @return StepAggregator
     * @throws \Exception
     */
    public function getParser($file, $format, $testOption = false)
    {
        $instance = null;

        switch ($format) {
            case 'csv':
                $instance = $this->csvParser($file, $testOption);
                break;
            default:
                throw new \Exception('Format not found');
        }

        return $instance;
    }

    /**
     * @return array
     */
    public function getParseErrors()
    {
        $result = [];

        if (method_exists($this->reader, 'hasErrors') && $this->reader->hasErrors()) {
            $result = array_keys($this->reader->getErrors());
        }

        return $result;
    }

    /**
     * Prepares Workflow with all filters, writers and converters
     *
     * @param Reader $reader
     * @param bool $testOption
     * @return StepAggregator
     */
    public function getWorkflow(Reader $reader, $testOption = false)
    {
        $workflow = new StepAggregator($reader);

        $this->writer->disableTruncate();
        $workflow->setSkipItemOnFailure(true);
        $workflow->addStep($this->getConverter(), 100);

        $filterStep = new FilterStep();
        $filterStep->add($this->getCallbackUniqueFilter(), 100);
        $filterStep->add($this->getCallbackConditionsFilter(), 90);
        $filterStep->add($this->getValidationFilter(), 80);

        $workflow->addStep($filterStep, 90);

        if (!$testOption) {
            $workflow->addWriter($this->writer);
        }

        return $workflow;
    }

    /**
     * converts default headers from file to database field names
     *
     * @return MappingStep
     */
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

    /**
     * @return ValidatorFilter
     */
    protected function getValidationFilter()
    {
        $validatorFilter = new ValidatorFilter($this->validator);

        $arrayOfConstraints = $this->helper->getConstraint(new Item());
        foreach ($arrayOfConstraints as $value) {
            $validatorFilter->add($value['field'], $value['constraint']);
        }

        $validatorFilter->throwExceptions();
        $validatorFilter->setStrict(false);

        return $validatorFilter;
    }

    /**
     * @return callable
     */
    protected function getCallbackUniqueFilter()
    {
        $callbackFilter = function ($data)
        {
            if(!isset($data['strProductCode'])) {
                throw new \Exception('Data is invalid');
            }

            if (isset($this->filter[$data['strProductCode']])) {
                $message = sprintf('Duplication product code - %s', $data['strProductCode']);
                throw new WriterException($message);
            } else {
                $this->filter[$data['strProductCode']] = true;
                $result = true;
            }

            return $result;
        };

        return $callbackFilter;
    }

    /**
     * @return callable
     */
    protected function getCallbackConditionsFilter()
    {
        $callbackFilter = function ($data)
        {
            if ($data['fltCost'] < self::ITEM_MIN_COST && $data['intStock'] < self::ITEM_MIN_STOCK) {
                $message = sprintf('Item cost less than 5 and product stock less than 10 - %s', $data['strProductCode']);
                throw new WriterException($message);
            } else {
                $this->filter[$data['strProductCode']] = true;
                $result = true;
            }

            return $result;
        };

        return $callbackFilter;
    }

    /**
     * @param string $file
     * @param bool $testOption
     * @return StepAggregator
     * @throws \Exception
     */
    protected function csvParser($file, $testOption = false)
    {
        try {
            $file = new \SplFileObject($file);
            $this->reader = new CsvReader($file);
            $this->reader->setHeaderRowNumber(0);
            $workflow = $this->getWorkflow($this->reader, $testOption);
            return $workflow;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}