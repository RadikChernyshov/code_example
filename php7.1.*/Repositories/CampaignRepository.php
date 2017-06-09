<?php
/**
 * Example
 *
 * @author Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace AppBundle\Repository;

use AppBundle\Entity\Campaign;
use AppBundle\Entity\CampaignLanguage;
use AppBundle\Entity\CampaignLocation;
use AppBundle\Entity\CampaignProperties;
use AppBundle\Entity\CampaignPropertyLocation;
use AppBundle\Entity\CampaignStatus;
use AppBundle\Entity\StatisticsCampaign;
use AppBundle\Entity\User;
use AppBundle\Entity\UserIndustry;
use AppBundle\Entity\UserProfile;
use AppBundle\Traits\RepositoryQueryTrait;
use AppBundle\Traits\RepositoryTrait;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CampaignRepository
 * @package AppBundle\Repository
 */
final class CampaignRepository extends EntityRepository
{
    use RepositoryTrait;
    use RepositoryQueryTrait;

    /**
     * @param int $campaignId
     * @param int $userId
     * @return null|Campaign
     * @throws EntityNotFoundException
     */
    public function getCampaignByUser(int $campaignId, int $userId): ?Campaign
    {
        $entity = $this->findOneBy(['id' => $campaignId, 'user' => $userId]);
        if (!$entity) {
            throw new EntityNotFoundException;
        }
        return $entity;
    }

    /**
     * @param array $campaignIds
     * @return array
     */
    public function getCampaigns(array $campaignIds): array
    {
        return $this->getQueryBuilder()->select('c')
            ->from(Campaign::class, 'c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $campaignIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $campaignId
     * @param int $userId
     * @return bool
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function removeCampaignsByUser(int $campaignId, int $userId): bool
    {
        $isDeleted = false;
        $campaignEntity = $this->getCampaignByUser($campaignId, $userId);
        if ($campaignEntity) {
            $campaignEntity->setIsDeleted(true);
            $this->flushEntity($campaignEntity);
            $isDeleted = true;
        }
        return $isDeleted;
    }

    /**
     * @param Campaign $campaignEntity
     * @param CampaignStatus $campaignStatusEntity
     * @return Campaign
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     * @throws EntityNotFoundException
     */
    public function flushCampaignStatus(Campaign $campaignEntity, CampaignStatus $campaignStatusEntity): Campaign
    {
        $campaignProperty = $campaignEntity->getProperty();
        if ($campaignProperty) {
            $campaignProperty->setStatus($campaignStatusEntity);
            $this->flushEntity($campaignProperty);
        }
        return $campaignEntity;
    }

    /**
     * @param array $campaignIds
     * @param int $activity
     * @return bool
     */
    public function flushCampaignActivityBatch(array $campaignIds, int $activity): bool
    {
        $result = false;
        if ((0 < count($campaignIds)) && in_array($activity, [Campaign::ACTIVE, Campaign::IN_ACTIVE], true)) {
            $this->getEntityManager()->createQueryBuilder()
                ->update(Campaign::class, 'c')
                ->set('c.is_deleted', $activity)
                ->where('c.id IN (:campaignIds)')
                ->setParameter('campaignIds', $campaignIds)
                ->getQuery()
                ->execute();
            $result = true;
        }
        return $result;
    }

    /**
     * @param User $userEntity
     * @param Form $form
     * @return Campaign
     * @throws HttpException
     * @throws \OutOfBoundsException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function create(User $userEntity, Form $form): Campaign
    {
        $campaignEntity = new Campaign;
        $campaignEntity->setTitle($form->get('title')->getData());
        $campaignEntity->setUser($userEntity);
        $campaignEntity->setIsDeleted(false);
        $campaignEntity->setCreatedAt(time());
        $scheduleAttributes = $form->get('schedule_attributes')->getData() ?? [];
        if (0 < count($scheduleAttributes)) {
            $campaignEntity->setIsScheduled(true);
            $campaignEntity->setScheduleAttributes($scheduleAttributes);
        } else {
            $campaignEntity->setIsScheduled(false);
        }
        $entityManager = $this->getEntityManager();
        $this->flushEntity($campaignEntity);
        $entityManager->getRepository(CampaignProperties::class)->create($userEntity, $campaignEntity, $form);
        $entityManager->getRepository(StatisticsCampaign::class)->create($campaignEntity);
        $entityManager->getRepository(UserProfile::class)->start($userEntity->getProfile());
        return $campaignEntity;
    }

    /**
     * @param Campaign $campaignEntity
     * @param Form $form
     * @return Campaign
     * @throws HttpException
     * @throws \OutOfBoundsException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function update(Campaign $campaignEntity, Form $form): Campaign
    {
        $entityManager = $this->getEntityManager();
        $campaignEntity->setTitle($form->get('title')->getData());
        $campaignEntity->setCreatedAt(time());
        $scheduleAttributes = $form->get('schedule_attributes')->getData() ?? [];
        if (0 < count($scheduleAttributes)) {
            $campaignEntity->setIsScheduled(true);
            $campaignEntity->setScheduleAttributes($scheduleAttributes);
        } else {
            $campaignEntity->setIsScheduled(false);
        }
        $this->flushEntity($campaignEntity);
        $entityManager->getRepository(CampaignProperties::class)->update($campaignEntity, $form);
        return $campaignEntity;
    }

    /**
     * @param User $userEntity
     * @param Campaign $campaignEntity
     * @return Campaign
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function copy(User $userEntity, Campaign $campaignEntity): Campaign
    {
        $newCampaignEntity = clone $campaignEntity;
        $newCampaignStatisticsEntity = clone $campaignEntity->getStatistics();
        $newCampaignPropertiesEntity = clone $campaignEntity->getProperties();
        $newCampaignEntity->setTitle(sprintf('Copy %s', $campaignEntity->getTitle()));
        $newCampaignEntity->setStatistics($newCampaignStatisticsEntity);
        $newCampaignEntity->setProperties($newCampaignPropertiesEntity);
        $newCampaignPropertiesEntity->setCampaign($newCampaignEntity);
        $newCampaignStatisticsEntity->setCampaign($newCampaignEntity);
        $this->getEntityManager()->persist($newCampaignEntity);
        $this->getEntityManager()->persist($newCampaignPropertiesEntity);
        $this->getEntityManager()->persist($newCampaignStatisticsEntity);
        $this->getEntityManager()->flush();
        return $newCampaignEntity;
    }

    /**
     * @param User $userEntity
     * @param array $campaignsId
     * @return array
     * @throws \OutOfBoundsException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function copyBatch(User $userEntity, array $campaignsId): array
    {
        $campaigns = [];
        $campaignsEntities = $this->getCampaigns($campaignsId);
        foreach ($campaignsEntities as $campaignEntity) {
            $campaigns[] = $this->copy($userEntity, $campaignEntity);
        }
        return $campaigns;
    }

    /**
     * @param array $campaignsId
     * @throws ORMInvalidArgumentException
     * @throws OptimisticLockException
     */
    public function deleteBatch(array $campaignsId): void
    {
        $entityManager = $this->getEntityManager();
        $campaignsEntities = $this->getCampaigns($campaignsId);
        foreach ($campaignsEntities as $entity) {
            $entity->setIsDeleted(true);
            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }

    /**
     * @return QueryBuilder
     */
    public function getBaseQuery(): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->select(['c'])
            ->from(Campaign::class, 'c')
            ->leftJoin(CampaignProperties::class, 'cp', 'WITH', 'cp.campaign_id = c.id')
            ->leftJoin(CampaignPropertyLocation::class, 'cpl', 'WITH', 'cpl.properties_id = cp.id')
            ->leftJoin(CampaignLocation::class, 'cln', 'WITH', 'cln.id = cpl.location_id')
            ->leftJoin(CampaignLanguage::class, 'clg', 'WITH', 'clg.id = cp.language_id')
            ->leftJoin(StatisticsCampaign::class, 'sc', 'WITH', 'sc.campaign_id = c.id')
            ->leftJoin(UserProfile::class, 'up', 'WITH', 'up.user_id = c.user_id')
            ->leftJoin(UserIndustry::class, 'ui', 'WITH', 'up.industry_id = ui.id')
            ->leftJoin(CampaignStatus::class, 'cs', 'WITH', 'cp.status_id = cs.id');
    }

    /**
     * @param Request $request
     * @return Query
     * @throws \InvalidArgumentException
     */
    public function getCampaignsQuery(Request $request): Query
    {
        return $this->setQueryConditions($this->getBaseQuery(), $request)->getQuery();
    }

    /**
     * @param User $userEntity
     * @param Request $request
     * @return Query
     * @throws \InvalidArgumentException
     */
    public function getUsersCampaignsQuery(User $userEntity, Request $request): Query
    {
        $campaignsBaseQuery = $this->getBaseQuery();
        $campaignsBaseQuery->where(
            $this->getQueryExpr()->eq($this->getRootTableAlias($campaignsBaseQuery) . '.user_id', $userEntity->getId())
        );
        return $this->setQueryConditions($campaignsBaseQuery, $request)->getQuery();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Request $request
     *
     * @return QueryBuilder
     * @throws \InvalidArgumentException
     */
    private function setQueryConditions(QueryBuilder $queryBuilder, Request $request): QueryBuilder
    {
        if ($this->isQueryParamExist('f_is_deleted', $request)) {
            $sortParams = $this->getQuerySortParams('f_is_deleted', $request);
            if (0 < count($sortParams)) {
                $expr = $this->getQueryExpr()->orX();
                foreach ($sortParams as $param) {
                    $expr->add(
                        $this->getQueryExpr()->eq($this->getRootTableAlias($queryBuilder) . '.is_deleted', $param)
                    );
                }
                $queryBuilder->andWhere($expr);
            }
        }
        if ($this->isQueryParamExist('id', $request)) {
            $queryBuilder->addOrderBy($this->getRootTableAlias($queryBuilder) . '.id', $this->getOrderQueryCondition('id', $request));
        }
        if ($this->isQueryParamExist('title', $request)) {
            $queryBuilder->addOrderBy(
                $this->getRootTableAlias($queryBuilder) . '.title',
                $this->getOrderQueryCondition('title', $request)
            );
        }
        if ($this->isQueryParamExist('created_at', $request)) {
            $queryBuilder->orderBy(
                $this->getRootTableAlias($queryBuilder) . '.created_at',
                $this->getOrderQueryCondition('created_at', $request)
            );
        }
        if ($this->isQueryParamExist('status', $request)) {
            $queryBuilder->addOrderBy('c.is_deleted', 'ASC');
            $queryBuilder->addOrderBy('cs.id', $this->getOrderQueryCondition('status', $request));
        }
        if ($this->isQueryParamExist('industry', $request)) {
            $queryBuilder->addOrderBy('ui.id', $this->getOrderQueryCondition('industry', $request));
        }
        if ($this->isQueryParamExist('location', $request)) {
            $queryBuilder->addOrderBy('cln.title', $this->getOrderQueryCondition('location', $request));
        }
        if ($this->isQueryParamExist('language', $request)) {
            $queryBuilder->addOrderBy('clg.title', $this->getOrderQueryCondition('language', $request));
        }
        if ($this->isQueryParamExist('leads_amount', $request)) {
            $queryBuilder->addOrderBy('sc.leads_amount', $this->getOrderQueryCondition('leads_amount', $request));
        }
        if ($this->isQueryParamExist('rate', $request)) {
            $queryBuilder->addOrderBy(
                'sc.rate',
                $this->getOrderQueryCondition('rate', $request)
            );
        }
        if ($this->isQueryParamExist('budget_daily', $request)) {
            $queryBuilder->addOrderBy(
                'cp.budget_daily',
                $this->getOrderQueryCondition('budget_daily', $request)
            );
        }
        if ($this->isQueryParamExist('sale_amount', $request)) {
            $queryBuilder->orderBy('sc.sale_amount', $this->getOrderQueryCondition('sale_amount', $request));
        }
        if ($this->isQueryParamExist('money_spent', $request)) {
            $queryBuilder->addOrderBy(
                'sc.money_spent',
                $this->getOrderQueryCondition('money_spent', $request)
            );
        }
        if ($this->isQueryParamExist('money_revenue', $request)) {
            $queryBuilder->addOrderBy(
                'sc.money_revenue',
                $this->getOrderQueryCondition('money_revenue', $request)
            );
        }
        return $queryBuilder;
    }
}
