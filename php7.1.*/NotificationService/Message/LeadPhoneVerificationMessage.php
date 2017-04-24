<?php
/**
 * Example
 *
 * @author     Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Message;

use AppBundle\Event\Notification\Interfaces\SmsMessageInterface;
use AppBundle\Event\Notification\Strategy\SmsStrategy;

/**
 * Class VerificationMessage
 *
 * @package AppBundle\Event\Notification\Message
 */
final class LeadPhoneVerificationMessage extends AbstractMessage implements SmsMessageInterface
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addStrategy(new SmsStrategy);
    }

    /**
     * @inheritDoc
     */
    public function getBody(): string
    {
        return sprintf('Your verification code is: %s', $this->getLeadDocument()->getShortCode());
    }
}
