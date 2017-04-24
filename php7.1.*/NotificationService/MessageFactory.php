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
use AppBundle\Event\Notification\Message\LeadEmailVerificationMessage;
use AppBundle\Event\Notification\Message\LeadPhoneVerificationMessage;
use AppBundle\Event\Notification\Message\LeadVerifiedMessage;
use AppBundle\Event\Notification\Message\RefoundMessage;
use AppBundle\Event\Notification\Message\ResetTokenMessage;
use AppBundle\Event\Notification\Message\VerificationMessage;
use AppBundle\Event\Notification\Message\WelcomeMessage;
use AppBundle\Event\Notification\Type\LeadEmailVerificationNotificationEvent;
use AppBundle\Event\Notification\Type\LeadPhoneNumberVerificationNotificationEvent;
use AppBundle\Event\Notification\Type\LeadVerifiedNotificationEvent;
use AppBundle\Event\Notification\Type\RefoundNotificationEvent;
use AppBundle\Event\Notification\Type\ResetTokenNotificationEvent;
use AppBundle\Event\Notification\Type\WelcomeNotificationEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class MessageFactory
 *
 * @package AppBundle\Event\Notification
 */
final class MessageFactory
{

    /**
     *
     */
    protected function __construct()
    {
    }

    /**
     * @param Event $event
     * @param ContainerInterface $container
     *
     * @return array
     * @throws \ErrorException
     */
    public static function getInstance(Event $event, ContainerInterface $container): array
    {
        $messageFactory = new MessageFactory;
        $messages = [];
        if ($event instanceof WelcomeNotificationEvent) {
            $messages[] = $messageFactory->getWelcomeMessage($event, $container);
            $messages[] = $messageFactory->getVerificationMessage($event, $container);
        } elseif ($event instanceof ResetTokenNotificationEvent) {
            $messages[] = $messageFactory->getResetTokenMessage($event, $container);
        } elseif ($event instanceof LeadPhoneNumberVerificationNotificationEvent) {
            $messages[] = $messageFactory->getPhoneNumberVerificationMessage($event, $container);
        } elseif ($event instanceof LeadEmailVerificationNotificationEvent) {
            $messages[] = $messageFactory->getLeadEmailAddressVerificationMessage($event, $container);
        } elseif ($event instanceof LeadVerifiedNotificationEvent) {
            $messages[] = $messageFactory->getLeadVerifiedMessage($event, $container);
        } elseif ($event instanceof RefoundNotificationEvent) {
            $messages[] = $messageFactory->getRefoundMessage($event, $container);
        } else {
            throw new \ErrorException('Event not found. Check the available events.');
        }
        return $messages;
    }

    /**
     * @param Event $event
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    protected function getWelcomeMessage(Event $event, ContainerInterface $container): AbstractMessage
    {
        $message = new WelcomeMessage;
        $message->setUserEntity($event->getUserEntity());
        $message->setEvent($event);
        $message->setContainer($container);
        return $message;
    }

    /**
     * @param Event $event
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    protected function getVerificationMessage(Event $event, ContainerInterface $container): AbstractMessage
    {
        $message = new VerificationMessage;
        $message->setUserEntity($event->getUserEntity());
        $message->setEvent($event);
        $message->setContainer($container);
        return $message;
    }

    /**
     * @param Event $event
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    protected function getResetTokenMessage(Event $event, ContainerInterface $container): AbstractMessage
    {
        $message = new ResetTokenMessage;
        $message->setUserEntity($event->getUserEntity());
        $message->setEvent($event);
        $message->setContainer($container);
        return $message;
    }

    /**
     * @param Event $event
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    protected function getPhoneNumberVerificationMessage(Event $event, ContainerInterface $container): AbstractMessage
    {
        $message = new LeadPhoneVerificationMessage();
        $message->setLeadDocument($event->getLeadDocument());
        $message->setEvent($event);
        $message->setContainer($container);
        return $message;
    }

    /**
     * @param Event $event
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    protected function getLeadEmailAddressVerificationMessage(
        Event $event,
        ContainerInterface $container
    ): AbstractMessage {
        $message = new LeadEmailVerificationMessage();
        $message->setLeadDocument($event->getLeadDocument());
        $message->setEvent($event);
        $message->setContainer($container);
        return $message;
    }

    /**
     * @param Event $event
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    protected function getLeadVerifiedMessage(
        Event $event,
        ContainerInterface $container
    ): AbstractMessage {
        $message = new LeadVerifiedMessage();
        $message->setLeadDocument($event->getLeadDocument());
        $message->setEvent($event);
        $message->setContainer($container);
        return $message;
    }

    /**
     * @param RefoundNotificationEvent $event
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    protected function getRefoundMessage(
        RefoundNotificationEvent $event,
        ContainerInterface $container
    ): AbstractMessage {
        $message = new RefoundMessage();
        $message->setAmount($event->getAmount());
        $message->setDescription($event->getDescription());
        $message->setUserEntity($event->getUserEntity());
        $message->setRecipientEntity($event->getRecipientEntity());
        $message->setEvent($event);
        $message->setContainer($container);
        return $message;
    }

    /**
     *
     */
    protected function __clone()
    {
    }
}
