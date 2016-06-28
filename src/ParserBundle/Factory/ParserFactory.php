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
     * @param string $type
     * @param bool $testOption
     * @return StepAggregator
     * @throws \Exception
     */
    public function getParser($type, $file, $testOption = false)
    {
        $instance = null;

        switch ($type) {
            case self::TYPE_CSV:
                $instance = $this->createCsvParser($file, $testOption);
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
     * @param string $file
     * @param bool $testOption
     * @return StepAggregator
     * @throws \Exception
     */
    protected function createCsvParser($file, $testOption = false)
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