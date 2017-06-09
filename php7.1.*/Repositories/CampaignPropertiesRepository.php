<?php
/**
 * Example
 *
 * @author     Eastern Peak development team <info@easternpeak.com>
 * @copyright  Copyright (c) 2016 Eastern Peak Software Inc. (http://easternpeak.com/)
 */

declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\Campaign;
use AppBundle\Entity\CampaignLanguage;
use AppBundle\Entity\CampaignLocation;
use AppBundle\Entity\CampaignPlatform;
use AppBundle\Entity\CampaignPlatformUserSettings;
use AppBundle\Entity\CampaignProperties;
use AppBundle\Entity\CampaignStatus;
use AppBundle\Entity\User;
use AppBundle\Traits\RepositoryTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use RestBundle\Util\HttpStatus;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CampaignPropertiesRepository
 * @package AppBundle\Repository
 */
final class CampaignPropertiesRepository extends EntityRepository
{
    use RepositoryTrait;

    /**
     * @param User $userEntity
     * @param Campaign $campaignEntity
     * @param Form $form
     * @return CampaignProperties
     * @throws HttpException
     * @throws \OutOfBoundsException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function create(User $userEntity, Campaign $campaignEntity, Form $form): CampaignProperties
    {
        $languageEntity = $this->getLanguageEntity($form);
        $propertyEntity = new CampaignProperties;
        $propertyEntity->setBudgetDaily($form->get('budget_daily')->getData());
        $propertyEntity->setLeadsDaily($form->get('leads_daily')->getData());
        $propertyEntity->setMaxLeadPrice($form->get('max_lead_price')->getData());
        $propertyEntity->setCampaign($campaignEntity);
        $propertyEntity->setLanguage($languageEntity);
        $propertyEntity->setStatus($this->getDefaultCampaignStatus());
        $propertyEntity->setLocations($this->getLocations($form->get('location')->getData()));
        $propertyEntity->setCreatedAt(time());
        $this->flushEntity($propertyEntity);
        return $propertyEntity;
    }

    /**
     * @param Campaign $campaignEntity
     * @param Form $form
     * @return CampaignProperties
     * @throws HttpException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public function update(Campaign $campaignEntity, Form $form): CampaignProperties
    {
        $languageEntity = $this->getLanguageEntity($form);
        $propertyEntity = $campaignEntity->getProperties();
        $propertyEntity->setBudgetDaily($form->get('budget_daily')->getData());
        $propertyEntity->setLeadsDaily($form->get('leads_daily')->getData());
        $propertyEntity->setMaxLeadPrice($form->get('max_lead_price')->getData());
        $propertyEntity->setLanguage($languageEntity);
        $propertyEntity->setLocations($this->getLocations($form->get('location')->getData()));
        $propertyEntity->setUpdatedAt(time());
        $this->flushEntity($propertyEntity);
        return $propertyEntity;
    }

    /**
     * @param array $locations
     * @return array
     */
    private function getLocations(array $locations): array
    {
        return $this->getQueryBuilder()
            ->select('l')
            ->from(CampaignLocation::class, 'l')
            ->where('l.id IN (:ids)')
            ->setParameter('ids', $locations)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $userEntity
     * @return CampaignPlatform
     * @throws HttpException
     */
    protected function getPlatformEntity(User $userEntity): CampaignPlatform
    {
        $platformSettingsEntity = $this->getEntityManager()
            ->getRepository(CampaignPlatformUserSettings::class)
            ->findOneBy(['user_id' => $userEntity->getId()]);
        if (!$platformSettingsEntity) {
            throw new HttpException(HttpStatus::NOT_FOUND, 'User Platform Settings not found');
        }
        return $platformSettingsEntity->getPlatform();
    }

    /**
     * @param Form $form
     * @return CampaignLanguage
     * @throws HttpException
     * @throws \OutOfBoundsException
     */
    protected function getLanguageEntity(Form $form): CampaignLanguage
    {
        $languageEntity = $this->getEntityManager()
            ->getRepository(CampaignLanguage::class)
            ->findOneBy(['id' => $form->get('language')->getData()]);
        if (!$languageEntity) {
            throw new HttpException(HttpStatus::NOT_FOUND, 'Platform Language not found');
        }
        return $languageEntity;
    }

    /**
     * @return CampaignStatus
     * @throws HttpException
     */
    public function getDefaultCampaignStatus(): CampaignStatus
    {
        $campaignStatus = $this->getEntityManager()->getRepository(CampaignStatus::class)->getDefaultStatus();
        if (!$campaignStatus) {
            throw new HttpException(HttpStatus::NOT_FOUND, 'Default Platform Status not found');
        }
        return $campaignStatus;
    }

    /**
     * @param CampaignStatus $campaignStatusEntity
     * @param array $campaignsIds
     * @return bool
     */
    public function setStatusBatch(CampaignStatus $campaignStatusEntity, array $campaignsIds): bool
    {
        $result = false;
        if (0 < count($campaignsIds)) {
            $result = (bool)$this->getQueryBuilder()->update(CampaignProperties::class, 'cp')
                ->set('cp.status_id', $campaignStatusEntity->getId())
                ->where('cp.campaign_id IN (:campaignsIds)')
                ->setParameter('campaignsIds', $campaignsIds)
                ->getQuery()
                ->execute();
        }
        return $result;
    }
}
