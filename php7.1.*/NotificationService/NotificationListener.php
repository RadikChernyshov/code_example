<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification;

use AppBundle\Event\Notification\Message\AbstractMessage;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class NotificationListener
 *
 * @package AppBundle\Event\Notification
 */
final class NotificationListener
{
    /**
     * @public const string
     */
    public const NOTIFICATION_EVENT = 'event.notification';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Event $event
     *
     * @throws Exception
     */
    public function onNotification(Event $event): void
    {
        $messages = MessageFactory::getInstance($event, $this->container);
        foreach ($messages as $message) {
            if ($message instanceof AbstractMessage) {
                $message->execute();
            }
        }
    }
}
