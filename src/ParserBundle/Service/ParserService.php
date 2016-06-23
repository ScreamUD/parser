<?php

namespace ParserBundle\Service;

use ParserBundle\Factory\ParserFactoryInterface;
use Doctrine\ORM\EntityManager;
use ParserBundle\Repository\ItemRepository;

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
     * Main method for parsing files
     *
     * @param string $file
     * @return bool|\Ddeboer\DataImport\Result
     * @throws \Ddeboer\DataImport\Exception\ExceptionInterface
     * @throws \Exception
     */
    public function parse($file)
    {
        $format = $this->getFileExtension($file);

        $workflow = $this->factory->getParser($file, $format);

        try {
            $result = $workflow->process();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        return $result;
    }

    /**
     * fastest method to get file extension
     *
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