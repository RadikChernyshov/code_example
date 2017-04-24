<?php
/**
 * Example
 *
 * @author     Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Type;

use AppBundle\Document\LeadDocument;

/**
 * Class LeadPhoneNumberVerificationNotificationEvent
 *
 * @package AppBundle\Event\Notification\Type
 */
final class LeadPhoneNumberVerificationNotificationEvent extends AbstractNotificationEvent
{
    /**
     * @var LeadDocument
     */
    protected $leadDocument;

    /**
     * @return LeadDocument
     */
    public function getLeadDocument(): LeadDocument
    {
        return $this->leadDocument;
    }

    /**
     * @param LeadDocument $leadDocument
     *
     * @return LeadPhoneNumberVerificationNotificationEvent
     */
    public function setLeadDocument(LeadDocument $leadDocument): self
    {
        $this->leadDocument = $leadDocument;
        return $this;
    }
}
