<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Type;

use Doctrine\ORM\EntityManager;

/**
 * Class WelcomeNotificationEvent
 *
 * @package AppBundle\Event\Notification\Type\Email
 */
final class WelcomeNotificationEvent extends AbstractNotificationEvent
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

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
}
