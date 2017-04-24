<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Type;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;

/**
 * Class RefoundNotificationEvent
 *
 * @package AppBundle\Event\Notification\Type\Email
 */
final class RefoundNotificationEvent extends AbstractNotificationEvent
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
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var User
     */
    protected $recipientEntity;

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount ?? 0;
    }

    /**
     * @param float $amount
     *
     * @return self
     */
    public function setAmount(?float $amount): self
    {
        $this->amount = $amount ?? 0;
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
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return self
     */
    public function setEntityManager(EntityManager $entityManager): self
    {
        $this->entityManager = $entityManager;
        return $this;
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
     * @return RefoundNotificationEvent
     */
    public function setRecipientEntity(User $recipientEntity): self
    {
        $this->recipientEntity = $recipientEntity;
        return $this;
    }
}
