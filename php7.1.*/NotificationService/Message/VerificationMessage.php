<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Message;

use AppBundle\Event\Notification\Interfaces\EmailMessageInterface;
use AppBundle\Event\Notification\Strategy\EmailStrategy;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class VerificationMessage
 *
 * @package AppBundle\Event\Notification\Message
 */
final class VerificationMessage extends AbstractMessage implements EmailMessageInterface
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
     */
    public function getSubject(): string
    {
        return 'User Email Verification';
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
                './mail/verification.html.twig',
                ['user' => $this->getUserEntity(), 'hash' => $this->generateHash()]
            );
    }

    /**
     * @return string
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function generateHash(): string
    {
        $userResetVerificationHash = $this->generateUserVerificationHash();
        $this->getContainer()
            ->get('snc_redis.default')
            ->rpush($userResetVerificationHash, $this->getUserEntity()->getId());
        return $userResetVerificationHash;
    }

    /**
     * @return string
     */
    private function generateUserVerificationHash(): string
    {
        return md5($this->getUserEntity()->getEmail());
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
