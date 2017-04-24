<?php
/**
 * Example
 *
 * @author     Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

declare(strict_types=1);

namespace AppBundle\Event\Notification\Strategy;

use AppBundle\Event\Notification\Interfaces\MessageStrategyInterface;
use AppBundle\Event\Notification\Message\AbstractMessage;
use SoapClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class SmsStrategy
 *
 * @package AppBundle\Event\Notification\Strategy
 */
final class SmsStrategy implements MessageStrategyInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     *
     * @return SmsStrategy
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @param AbstractMessage $message
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function execute(AbstractMessage $message): bool
    {
        return (bool)$this->getSMSGatewayClient()->SimpleSMSsend([
            'PhoneNumber' => $message->getLeadDocument()->getPhoneNumber(),
            'LicenseKey' => $this->container->getParameter('sms.gateway.licence.key'),
            'Message' => $message->getBody()
        ]);
    }

    /**
     * @return SoapClient
     * @throws InvalidArgumentException
     */
    private function getSMSGatewayClient(): SoapClient
    {
        return new SoapClient($this->container->getParameter('sms.gateway.address'));
    }
}
