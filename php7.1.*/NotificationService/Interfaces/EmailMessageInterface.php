<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Interfaces;

use AppBundle\Document\LeadDocument;
use AppBundle\Entity\User;

/**
 * Interface EmailMessageInterface
 *
 * @package AppBundle\Event\Notification\Interfaces
 */
interface EmailMessageInterface
{
    /**
     * @return User
     */
    public function getUserEntity(): ?User;

    /**
     * @return LeadDocument
     */
    public function getLeadDocument(): ?LeadDocument;

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
