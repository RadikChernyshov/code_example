<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 *
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Interfaces;

/**
 * Interface DbMessageInterface
 *
 * @package AppBundle\Event\Notification\Interfaces
 */
interface DbMessageInterface
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return array
     */
    public function getAttributes(): array;
}
