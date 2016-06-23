<?php

namespace ParserBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use ParserBundle\Entity\Item;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;

class ValidationSubscriber implements EventSubscriber
{

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $statistic = array('error' => 0, 'success' => 0);

    /**
     * @var bool
     */
    protected $isTest = false;

    /**
     * ValidationSubscriber constructor.
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * Array with SubscribedEvents
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'postFlush',
            'testEvent',
            'errorsCountEvent'
        );
    }

    /**
     * This event used for correct validate Item insert to database,
     * these manipulations used because Workflow Validator(very Old Version) does not work
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Item) {
                $errors = $this->validator->validate($entity);

                if (0 !== count($errors)) {
                    $this->handleErrors($errors, $entity);

                    $em->detach($entity);
                    $this->statistic['error']++;
                } else {
                    $this->statistic['success']++;
                }

                if ($this->isTest) {
                    $em->detach($entity);
                }
            }
        }
    }

    /**
     * Used to write all result counts about success and error items
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $message = sprintf('%s products were imported, %s products with error', $this->statistic['success'], $this->statistic['error']);

        $this->logger->alert($message);
    }

    /**
     * Used during insertion into the database and write errors
     *
     * @param ConstraintViolationListInterface $errors
     * @param Item $item
     */
    protected function handleErrors(ConstraintViolationListInterface $errors, Item $item)
    {
        $err = array();

        foreach ($errors as $error) {
            $err[] = $error->getMessage();
        }

        $message = sprintf('The product %s (code: %s) was not imported. Errors:', $item->getStrProductName(), $item->getStrProductCode());

        $this->logger->warning($message, $err);
    }

    /**
     * Custom event which used for test mode
     * and does not allow insert items to database
     */
    public function testEvent()
    {
        $this->isTest = true;
    }

    /**
     * this event use to show correct count
     * errors which added not via Subscriber
     */
    public function errorsCountEvent()
    {
        $this->statistic['error']++;
    }
}

