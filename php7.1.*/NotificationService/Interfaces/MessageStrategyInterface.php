<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Interfaces;

use AppBundle\Event\Notification\Message\AbstractMessage;

/**
 * Interface MessageStrategyInterface
 *
 * @package AppBundle\Event\Notification\Interfaces
 */
interface MessageStrategyInterface
{
    /**
     * @param AbstractMessage $message
     *
     * @return bool
     */
    public function execute(AbstractMessage $message): bool;
}
