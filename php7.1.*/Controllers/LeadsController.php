<?php
/**
 * Example
 *
 * @author Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace RestBundle\Controller;

use AppBundle\Document\LeadDocument;
use AppBundle\Entity\Offer;
use AppBundle\Entity\User;
use AppBundle\Event\Notification\NotificationListener;
use AppBundle\Event\Notification\Type\LeadEmailVerificationNotificationEvent;
use AppBundle\Event\Notification\Type\LeadPhoneNumberVerificationNotificationEvent;
use FOS\RestBundle\Controller\FOSRestController;
use RestBundle\Form\CampaignLeadType;
use RestBundle\Traits\DocumentControllerTrait;
use RestBundle\Traits\EventControllerTrait;
use RestBundle\Traits\FindDocumentControllerTrait;
use RestBundle\Traits\FindEntityControllerTrait;
use RestBundle\Traits\RepositoryControllerTrait;
use RestBundle\Traits\RequestControllerTrait;
use RestBundle\Traits\ResponseControllerTrait;
use RestBundle\Util\HttpStatus;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class LeadsController
 * @package RestBundle\Controller
 */
final class LeadsController extends FOSRestController
{
    use RequestControllerTrait,
        ResponseControllerTrait,
        RepositoryControllerTrait,
        FindDocumentControllerTrait,
        FindEntityControllerTrait,
        DocumentControllerTrait,
        EventControllerTrait;

    /**
     * GET /leads/{leadId}
     *
     * @param int $leadId
     * @return JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws AlreadySubmittedException
     * @throws \LogicException
     */
    public function getLeadAction(int $leadId): JsonResponse
    {
        return $this->getJsonResponse($this->findEntity(LeadDocument::class, $leadId));
    }

    /**
     * POST /leads
     *
     * @return JsonResponse
     * @throws HttpException
     * @throws \InvalidArgumentException
     * @throws AlreadySubmittedException
     * @throws \LogicException
     */
    public function postLeadAction(): JsonResponse
    {
        $leadForm = $this->createForm(CampaignLeadType::class);
        $leadForm->submit($this->getPost($this->getRequest()));
        if ($leadForm->isValid()) {
            $leadDocument = $this->getDocumentRepository(LeadDocument::class)
                ->create($leadForm, $this->getDoctrine()->getManager());
            $offerEntity = $this->findEntity(Offer::class, $leadDocument->getOffer()['id']);
            if ($offerEntity->isNeedEmailAddressVerification()) {
                $event = (new LeadEmailVerificationNotificationEvent)->setLeadDocument($leadDocument);
                $this->dispatchEvent(NotificationListener::NOTIFICATION_EVENT, $event);
            } elseif ($offerEntity->isNeedPhoneNumberVerification()) {
                $event = (new LeadPhoneNumberVerificationNotificationEvent)->setLeadDocument($leadDocument);
                $this->dispatchEvent(NotificationListener::NOTIFICATION_EVENT, $event);
            }
            $response = $this->getJsonResponse($leadDocument);
        } else {
            $response = $this->getJsonResponse($this->getFormErrorsResponse($leadForm), HttpStatus::BAD_REQUEST);
        }
        return $response;
    }

    /**
     * PUT /leads/{leadId}
     *
     * @param int $leadId
     * @return JsonResponse
     * @throws HttpException
     * @throws \InvalidArgumentException
     * @throws AlreadySubmittedException
     * @throws \LogicException
     */
    public function putLeadAction(int $leadId): JsonResponse
    {
        $leadEntity = $this->findDocument(LeadDocument::class, $leadId);
        $leadForm = $this->createForm(CampaignLeadType::class);
        $leadForm->submit($this->getPost($this->getRequest()));
        if ($leadForm->isValid()) {
            $leadEntity = $this->getDocumentRepository(LeadDocument::class)
                ->update($leadEntity, $leadForm, $this->getDoctrine()->getManager());
            $response = $this->getJsonResponse($leadEntity);
        } else {
            $response = $this->getJsonResponse($this->getFormErrorsResponse($leadForm), HttpStatus::BAD_REQUEST);
        }
        return $response;
    }
}
