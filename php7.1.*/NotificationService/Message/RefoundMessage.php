<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Message;

use AppBundle\Entity\User;
use AppBundle\Event\Notification\Interfaces\EmailMessageInterface;
use AppBundle\Event\Notification\Strategy\EmailStrategy;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class RefoundMessage
 *
 * @package AppBundle\Event\Notification\Message
 */
final class RefoundMessage extends AbstractMessage implements EmailMessageInterface
{
    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var User
     */
    protected $recipientEntity;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addStrategy(new EmailStrategy);
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return self
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return sprintf(
            'Buyer#%s is requesting a refund - please contact him/her',
            $this->getUserEntity()->getFirstName()
        );
    }

    /**
     * @inheritDoc
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \OutOfBoundsException
     */
    public function getBody(): string
    {
        return $this->getContainer()
            ->get('templating')
            ->render(
                './mail/refound.html.twig',
                [
                    'user' => $this->getUserEntity(),
                    'administrator' => $this->getRecipientEntity(),
                    'body' => $this->getDescription()
                ]
            );
    }

    /**
     * @return User
     */
    public function getRecipientEntity(): User
    {
        return $this->recipientEntity;
    }

    /**
     * @param User $recipientEntity
     *
     * @return RefoundMessage
     */
    public function setRecipientEntity(User $recipientEntity): self
    {
        $this->recipientEntity = $recipientEntity;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
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
