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
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractNotificationEvent
 *
 * @package AppBundle\Event\Notification\Type
 */
abstract class AbstractNotificationEvent extends Event
{
    /**
     * @var User
     */
    protected $userEntity;

    /**
     * User
     */
    public function getUserEntity(): User
    {
        return $this->userEntity;
    }

    /**
     * @param User $userEntity
     *
     * @return self
     */
    public function setUserEntity(User $userEntity): AbstractNotificationEvent
    {
        $this->userEntity = $userEntity;
        return $this;
    }
}
