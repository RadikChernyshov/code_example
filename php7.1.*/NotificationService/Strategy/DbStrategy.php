<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Strategy;

use AppBundle\Entity\FeedEvent;
use AppBundle\Event\Notification\Interfaces\MessageStrategyInterface;
use AppBundle\Event\Notification\Message\AbstractMessage;
use AppBundle\Repository\FeedEventRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class DbStrategy
 *
 * @package AppBundle\Event\Notification\Strategy
 */
final class DbStrategy implements MessageStrategyInterface
{
    /**
     * @inheritDoc
     *
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function execute(AbstractMessage $message): bool
    {
        return (bool)$this->getTargetRepository($message)->createFromMessage($message);
    }

    /**
     * @param AbstractMessage $message
     *
     * @return FeedEventRepository
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    protected function getTargetRepository(AbstractMessage $message): FeedEventRepository
    {
        return $message->getContainer()->get('doctrine.orm.entity_manager')->getRepository(FeedEvent::class);
    }
}
