<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

namespace AppBundle\Event\Notification\Strategy;

use AppBundle\Event\Notification\Interfaces\MessageStrategyInterface;
use AppBundle\Event\Notification\Interfaces\QueueMessageInterface;
use AppBundle\Event\Notification\Message\AbstractMessage;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class QueueStrategy
 *
 * @package AppBundle\Event\Notification\Strategy
 */
final class QueueStrategy implements MessageStrategyInterface
{
    /**
     * @public const string
     */
    private const QUEUE_COMPONENT_TITLE = 'snc_redis.default';

    /**
     * @inheritDoc
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function execute(AbstractMessage $message): bool
    {
        return (bool)$message->getContainer()
            ->get(self::QUEUE_COMPONENT_TITLE)
            ->rpush(QueueMessageInterface::QUEUE_EMAILS, $this->getQueueMessage($message));
    }

    /**
     * @param QueueMessageInterface $message
     *
     * @return string
     */
    public function getQueueMessage(QueueMessageInterface $message): string
    {
        return json_encode([
            'email' => $message->getUserEntity()->getEmail(),
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'from' => $message->getFrom()
        ]);
    }
}
