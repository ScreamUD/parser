<?php

namespace ParserBundle\Service;

use ParserBundle\Factory\ParserFactory;
use Doctrine\ORM\EntityManager;
use ParserBundle\Repository\ItemRepository;
use ParserBundle\Parser\Result;

class ParserService
{
    /**
     * @var ParserFactory
     */
    protected $factory;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * ParserService constructor.
     * @param ParserFactory $factory
     * @param EntityManager $em
     */
    public function __construct(ParserFactory $factory, EntityManager $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    /**
     * @param string $file
     * @param array $restrictions
     * @param bool $testOption
     * @return Result
     * @throws \Exception
     */
    public function parse($file, array $restrictions, $testOption = false)
    {
        $type = $this->getFileExtension($file);

        $parser = $this->factory->createParser($type, $restrictions, $testOption);
        
        $result = $parser->process($file);

        return $result;
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
