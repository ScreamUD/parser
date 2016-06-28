<?php

namespace ParserBundle\Service;

use ParserBundle\Factory\ParserFactoryInterface;
use Doctrine\ORM\EntityManager;
use ParserBundle\Repository\ItemRepository;
use Ddeboer\DataImport\Result;

class ParserService
{
    /**
     * @var ParserFactoryInterface
     */
    protected $factory;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * ParserService constructor.
     * @param ParserFactoryInterface $factory
     * @param EntityManager $em
     */
    public function __construct(ParserFactoryInterface $factory, EntityManager $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    /**
     * @param string $file
     * @param bool $testOption
     * @return Result
     * @throws \Exception
     */
    public function parse($file, $testOption = false)
    {
        $format = $this->getFileExtension($file);

        $workflow = $this->factory->getParser($file, $format, $testOption);

        try {
            $result = $workflow->process();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getParseErrors()
    {
        return $this->factory->getParseErrors();
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getFileExtension($file)
    {
        return substr(strrchr($file, '.'), 1);
    }

    /**
     * Clear all data from table by repository Item
     */
    public function clearItemTable()
    {
        /** @var ItemRepository $itemRepository */
        $itemRepository = $this->em->getRepository('ParserBundle:Item');
        $itemRepository->clearTable();
    }
}