<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

namespace AppBundle\Event\Notification\Message;

use AppBundle\Event\Notification\Interfaces\EmailMessageInterface;
use AppBundle\Event\Notification\Interfaces\QueueMessageInterface;
use AppBundle\Event\Notification\Strategy\EmailStrategy;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ResetTokenMessage
 *
 * @package AppBundle\Event\Notification\Message
 */
final class ResetTokenMessage extends AbstractMessage implements EmailMessageInterface, QueueMessageInterface
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addStrategy(new EmailStrategy);
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
            ->render(
                './mail/reset_token.html.twig',
                [
                    'user' => $this->getUserEntity(),
                    'token' => $this->getEvent()->getResetTokenService()->getToken()
                ]
            );
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return 'Example – Reset Password';
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
