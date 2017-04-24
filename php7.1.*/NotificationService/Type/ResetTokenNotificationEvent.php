<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Type;

use RestBundle\Util\Service\RestTokenService;

/**
 * Class ResetTokenEmail
 *
 * @package RestBundle\Event\Notification\Type\Email
 */
final class ResetTokenNotificationEvent extends AbstractNotificationEvent
{
    /**
     * @var RestTokenService
     */
    protected $resetTokenService;

    /**
     * @return RestTokenService
     */
    public function getResetTokenService(): RestTokenService
    {
        return $this->resetTokenService;
    }

    /**
     * @param RestTokenService $resetTokenService
     *
     * @return ResetTokenNotificationEvent
     */
    public function setResetTokenService(RestTokenService $resetTokenService): self
    {
        $this->resetTokenService = $resetTokenService;
        return $this;
    }
}
