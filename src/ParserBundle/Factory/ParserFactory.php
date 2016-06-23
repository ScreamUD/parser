<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Reader\ReaderInterface;
use Ddeboer\DataImport\Filter\CallbackFilter;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Ddeboer\DataImport\ItemConverter\MappingItemConverter;
use Ddeboer\DataImport\ItemConverter\ItemConverterInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class ParserFactory
 * @package ParserBundle\Services\CsvParser
 */
class ParserFactory implements ParserFactoryInterface
{
    /**
     * filter for unique data
     *
     * @var array
     */
    protected $filter = array();

    /**
     * @var DoctrineWriter
     */
    protected $writer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var MappingItemConverter
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
     * @var \Doctrine\Common\EventManager
     */
    protected $evm;

    /**
     * ParserFactory constructor.
     * @param DoctrineWriter $writer
     * @param ValidatorInterface $validator
     * @param MappingItemConverter $converter
     * @param LoggerInterface $logger
     * @param EntityManager $em
     */
    public function __construct(
        DoctrineWriter $writer,
        ValidatorInterface $validator,
        MappingItemConverter $converter,
        LoggerInterface $logger,
        EntityManager $em
    )
    {
        $this->writer = $writer;
        $this->validator = $validator;
        $this->converter = $converter;
        $this->logger = $logger;
        $this->em = $em;
        $this->evm = $em->getEventManager();
    }

    /**
     * get parser via file extension format
     *
     * @param string $file
     * @param string $format
     * @return Workflow
     * @throws \Exception
     */
    public function getParser($file, $format)
    {
        $instance = null;

        switch ($format) {
            case 'csv':
                $instance = $this->csvParser($file);
                break;
            default:
                throw new \Exception('Format not found');
        }

        return $instance;
    }

    /**
     * This method get CsvReader and makes
     * Workflow via reader with prepared data to insertion
     *
     * @param string $file
     * @return Workflow
     * @throws \Exception
     */
    protected function csvParser($file)
    {
        try {
            $file = new \SplFileObject($file);
            $reader = new CsvReader($file);
            $reader->setHeaderRowNumber(0);

            if ($reader->hasErrors()) {
                $lines = array_keys($reader->getErrors());

                $this->logger->error('Parse error on lines:', $lines);

                for ($i = 0; $i < count($lines); $i++) {
                    $this->evm->dispatchEvent('errorsCountEvent');
                }
            }

            $workflow = $this->getWorkflow($reader);

            return $workflow;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Prepares Workflow with all filters, writers and converters
     *
     * @param ReaderInterface $reader
     * @return Workflow
     */
    public function getWorkflow(ReaderInterface $reader)
    {
        $workflow = new Workflow($reader);

        $this->writer->setBatchSize(count($reader));
        $this->writer->disableTruncate();
        $workflow->addFilter($this->getCallbackUniqueFilter());
        $workflow->addItemConverter($this->getConverter());
        $workflow->addWriter($this->writer);

        return $workflow;
    }

    /**
     * filter to check duplicate 'Product Code' fields
     *
     * @return CallbackFilter
     */
    protected function getCallbackUniqueFilter()
    {
        $callbackFilter = new CallbackFilter(function ($data)
        {
            if (isset($this->filter[$data['Product Code']])) {
                $this->logger->alert('Duplication product code ' . $data['Product Code']);
                $this->evm->dispatchEvent('errorsCountEvent');
                $result = false;
            } else {
                $this->filter[$data['Product Code']] = true;
                $result = true;
            }

            return $result;
        });

        return $callbackFilter;
    }

    /**
     * method for convert default headers from file to database field names
     *
     * @return ItemConverterInterface
     */
    public function getConverter()
    {
        return $this->converter
            ->addMapping('Product Code', 'strProductCode')
            ->addMapping('Product Name', 'strProductName')
            ->addMapping('Product Description', 'strProductDesc')
            ->addMapping('Stock', 'intStock')
            ->addMapping('Cost in GBP', 'fltCost')
            ->addMapping('Discontinued', 'dtmDiscontinued');
    }
}