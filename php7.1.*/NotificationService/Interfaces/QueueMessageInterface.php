<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Interfaces;

use AppBundle\Entity\User;

/**
 * Interface QueueMessageInterface
 *
 * @package AppBundle\Event\Notification\Interfaces
 */
interface QueueMessageInterface
{
    /**
     * @public const string
     */
    public const QUEUE_EMAILS = 'queue-emails';

    /**
     * @return User
     */
    public function getUserEntity(): ?User;

    /**
     * @return string
     */
    public function getBody(): string;

    /**
     * @return string
     */
    public function getSubject(): string;

    /**
     * @return string
     */
    public function getFrom(): string;
}
