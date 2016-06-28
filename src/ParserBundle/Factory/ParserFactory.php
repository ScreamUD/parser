<?php

namespace ParserBundle\Factory;

use Ddeboer\DataImport\Step\MappingStep;
use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use ParserBundle\Exception\Exception;
use ParserBundle\Helper\ConstraintInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class ParserFactory
 * @package ParserBundle\Factory
 */
class ParserFactory
{
    const TYPE_CSV = 'csv';

    /**
     * @var DoctrineWriter
     */
    protected $writer;
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var ConstraintInterface
     */
    protected $helper;

    /**
     * ParserFactory constructor.
     * @param DoctrineWriter $writer
     * @param ValidatorInterface $validator
     * @param ConstraintInterface $helper
     */
    public function __construct(
        DoctrineWriter $writer,
        ValidatorInterface $validator,
        ConstraintInterface $helper
    )
    {
        $this->writer = $writer;
        $this->validator = $validator;
        $this->helper = $helper;
    }

    /**
     * get parser via file extension format
     *
     * @param string $type
     * @param array $restrictions
     * @param bool $testOption
     * @return Parser
     * @throws Exception
     */
    public function createParser($type, array $restrictions, $testOption)
    {
        switch ($type) {
            case self::TYPE_CSV:
                $parser = new CsvParser();
                break;
            default:
                throw new Exception('Format not found');
        }


        $this->setDepends($parser);
        $this->setRestrictions($parser, $restrictions);
        $parser->setTestOption($testOption);

        return $parser;
    }

    /**
     * @param \ParserBundle\Factory\Parser $parser
     * @return $this
     */
    protected function setDepends(Parser $parser)
    {
        $parser->setWriter($this->writer);
        $parser->setConstraintHelper($this->helper);
        $parser->setValidator($this->validator);

        $parser->setConverter($this->converter);

        return $this;
    }

    /**
     * @param \ParserBundle\Factory\Parser $parser
     * @param array $restrictions
     * @return $this
     */
    protected function setRestrictions(Parser $parser, array $restrictions)
    {
        $parser->setCost($restrictions['cost']);
        $parser->setStock($restrictions['stock']);

        return $this;
    }
}
