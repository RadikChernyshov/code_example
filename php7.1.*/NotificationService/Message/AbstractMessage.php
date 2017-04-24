<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Message;

use AppBundle\Document\LeadDocument;
use AppBundle\Entity\User;
use AppBundle\Event\Notification\Interfaces\MessageStrategyInterface;
use AppBundle\Util\Service\LeadExport\Strategy\AbstractStrategy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractMessage
 *
 * @package AppBundle\Event\Notification\Message
 */
abstract class AbstractMessage
{
    /**
     * @var Event
     */
    protected $event;
    /**
     * @var User
     */
    protected $userEntity;
    /**
     * @var LeadDocument
     */
    protected $leadDocument;
    /**
     * @var array
     */
    private $strategies;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param MessageStrategyInterface $strategy
     *
     * @return AbstractMessage
     */
    public function addStrategy(MessageStrategyInterface $strategy): self
    {
        $this->strategies[] = $strategy;
        return $this;
    }

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
     * @return AbstractMessage
     */
    public function setLeadDocument(LeadDocument $leadDocument): AbstractMessage
    {
        $this->leadDocument = $leadDocument;
        return $this;
    }

    /**
     * @return User
     */
    public function getUserEntity(): ?User
    {
        return $this->userEntity;
    }

    /**
     * @param User $userEntity
     *
     * @return AbstractMessage
     */
    public function setUserEntity(User $userEntity): AbstractMessage
    {
        $this->userEntity = $userEntity;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return AbstractMessage
     */
    public function setEvent(Event $event): AbstractMessage
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return array
     */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return AbstractMessage
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     *
     */
    public function execute(): void
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy instanceof MessageStrategyInterface) {
                $strategy->execute($this);
            }
        }
    }
}
