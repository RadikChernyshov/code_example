<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Message;

use AppBundle\Event\Notification\Interfaces\DbMessageInterface;
use AppBundle\Event\Notification\Interfaces\EmailMessageInterface;
use AppBundle\Event\Notification\Interfaces\QueueMessageInterface;
use AppBundle\Event\Notification\Strategy\DbStrategy;
use AppBundle\Event\Notification\Strategy\EmailStrategy;
use AppBundle\Repository\FeedEventTypeRepository;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class WelcomeMessage
 *
 * @package AppBundle\Event\Notification\Message
 */
final class WelcomeMessage extends AbstractMessage implements
    DbMessageInterface,
    QueueMessageInterface,
    EmailMessageInterface
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addStrategy(new DbStrategy);
        $this->addStrategy(new EmailStrategy);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Welcome to Leadsbasket';
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return 'Welcome to Example';
    }

    /**
     * @inheritDoc
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function getBody(): string
    {
        return $this->getContainer()
            ->get('templating')
            ->render('./mail/welcome.html.twig', ['user' => $this->getUserEntity()]);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return FeedEventTypeRepository::TYPE_ACCOUNT;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return ['userId' => $this->userEntity->getId()];
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function getFrom(): string
    {
        return $this->getContainer()->getParameter('email.from.address');
    }
}
