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

use AppBundle\Document\LeadDocument;
use AppBundle\Event\Notification\Interfaces\EmailMessageInterface;
use AppBundle\Event\Notification\Strategy\EmailStrategy;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class LeadVerifiedMessage
 *
 * @package AppBundle\Event\Notification\Message
 */
final class LeadVerifiedMessage extends AbstractMessage implements EmailMessageInterface
{

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->addStrategy(new EmailStrategy);
    }

    /**
     * @inheritDoc
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function getBody(): string
    {
        return $this->getContainer()
            ->get('templating')
            ->render(
                './mail/lead.verified.html.twig',
                [
                    'leadDocument' => $this->getLeadDocument()
                ]
            );
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return (string)$this->getLeadDocument()->getOffer()['email_subject'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getFrom(): string
    {
        return (string)$this->getLeadDocument()->getOffer()['email_behalf_name'] ?? '';
    }
}
