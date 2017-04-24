<?php
/**
 * Example
 *
 * @author    Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Strategy;

use AppBundle\Event\Notification\Interfaces\MessageStrategyInterface;
use AppBundle\Event\Notification\Message\AbstractMessage;
use AppBundle\Event\Notification\Message\RefoundMessage;
use Swift_Message as SwiftMessage;
use Swift_Mime_MimePart as SwiftMimePart;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Class EmailStrategy
 *
 * @package AppBundle\Event\Notification\Strategy
 */
final class EmailStrategy implements MessageStrategyInterface
{
    /**
     * @const string
     */
    private const EMAIL_CONTENT_TYPE = 'text/html';

    /**
     * @public const string
     */
    private const MAILER_SERVICE_TITLE = 'mailer';

    /**
     * @inheritDoc
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws InvalidArgumentException
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     */
    public function execute(AbstractMessage $message): bool
    {
        return (bool)$message->getContainer()
            ->get(self::MAILER_SERVICE_TITLE)
            ->send($this->getSwiftMessage($message));
    }

    /**
     * @param AbstractMessage $message
     * @param string $contentType
     *
     * @return SwiftMimePart
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     */
    protected function getSwiftMessage(
        AbstractMessage $message,
        string $contentType = self::EMAIL_CONTENT_TYPE
    ): SwiftMimePart {
        return SwiftMessage::newInstance()
            ->setSubject($message->getSubject())
            ->setTo($this->getRecipientEmailAddress($message))
            ->setFrom($this->getSenderEmailAddress($message))
            ->setBody($message->getBody(), $contentType);
    }

    /**
     * @param AbstractMessage $message
     *
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getRecipientEmailAddress(AbstractMessage $message): string
    {
        if ($message instanceof RefoundMessage) {
            $recipientEmailAddress = $message->getContainer()->getParameter('email.refounds.address');
        } else {
            $userEntity = $message->getUserEntity() ?? $message->getLeadDocument();
            $recipientEmailAddress = $userEntity->getEmail();
        }
        return $recipientEmailAddress;
    }

    /**
     * @param AbstractMessage $message
     *
     * @return string
     * @throws MissingOptionsException
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     */
    protected function getSenderEmailAddress(AbstractMessage $message): string
    {
        if ($this->isEmailAddressValid($message, $message->getFrom())) {
            $senderEmailAddress = $message->getFrom();
        } else {
            $senderEmailAddress = $message->getContainer()->getParameter('email.from.address');
        }
        return $senderEmailAddress;
    }

    /**
     * @param AbstractMessage $message
     * @param string $emailAddress
     *
     * @return bool
     * @throws MissingOptionsException
     * @throws InvalidOptionsException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function isEmailAddressValid(AbstractMessage $message, string $emailAddress): bool
    {
        $validationErrors = $message->getContainer()
            ->get('validator')
            ->validate($emailAddress, new Email());
        return (0 === count($validationErrors));
    }
}
