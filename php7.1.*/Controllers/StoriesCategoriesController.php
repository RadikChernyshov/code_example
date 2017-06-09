<?php
/**
 * Example
 *
 * @author Rodion Chernyshov
 * @copyright Copyright (c) 2017
 */

declare(strict_types=1);

namespace RestBundle\Controller;

use AppBundle\Entity\Story;
use AppBundle\Entity\StoryCategory;
use FOS\RestBundle\Controller\FOSRestController;
use LogicException;
use RestBundle\Form\StoryCategoryType;
use RestBundle\Traits\FindEntityControllerTrait;
use RestBundle\Traits\RepositoryControllerTrait;
use RestBundle\Traits\RequestControllerTrait;
use RestBundle\Traits\ResponseControllerTrait;
use RestBundle\Util\HttpStatus;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class StoriesCategoriesController
 * @package RestBundle\Controller
 */
final class StoriesCategoriesController extends FOSRestController
{
    use RequestControllerTrait;
    use ResponseControllerTrait;
    use RepositoryControllerTrait;
    use FindEntityControllerTrait;

    /**
     * GET /stories-categories
     *
     * @return JsonResponse
     * @throws \Exception
     * @throws HttpException
     * @throws \LogicException
     */
    public function getCategoriesAction(): JsonResponse
    {
        return $this->getJsonResponse($this->getRepository(StoryCategory::class)->getList($this->getRequest()));
    }

    /**
     * GET /stories-categories/{categoryId}
     *
     * @param int $categoryId
     * @return JsonResponse
     * @throws HttpException
     * @throws \LogicException
     */
    public function getCategoryAction(int $categoryId): JsonResponse
    {
        return $this->getJsonResponse($this->findEntity(StoryCategory::class, $categoryId));
    }

    /**
     * [PUT] /stories-categories/{categoryId}
     *
     * @param int $categoryId
     * @return JsonResponse
     * @throws AlreadySubmittedException
     * @throws HttpException
     * @throws LogicException
     */
    public function putCategoryAction(int $categoryId): JsonResponse
    {
        $storyForm = $this->createForm(StoryCategoryType::class);
        $storyForm->submit($this->getPost($this->getRequest()));
        if ($storyForm->isValid()) {
            $storyEntity = $this->findEntity(StoryCategory::class, $categoryId);
            $response = $this->getJsonResponseCreated(
                $this->getRepository(StoryCategory::class)->update($storyEntity, $storyForm)
            );
        } else {
            $response = $this->getJsonResponse($this->getFormErrorsResponse($storyForm), HttpStatus::BAD_REQUEST);
        }
        return $response;
    }

    /**
     * POST /stories-categories
     *
     * @return JsonResponse
     * @throws AlreadySubmittedException
     * @throws HttpException
     * @throws LogicException
     */
    public function postCategoryAction(): JsonResponse
    {
        $storyForm = $this->createForm(StoryCategoryType::class);
        $storyForm->submit($this->getPost($this->getRequest()));
        if ($storyForm->isValid()) {
            $response = $this->getJsonResponseCreated($this->getRepository(StoryCategory::class)->create($storyForm));
        } else {
            $response = $this->getJsonResponse($this->getFormErrorsResponse($storyForm), HttpStatus::BAD_REQUEST);
        }
        return $response;
    }

    /**
     * DELETE /stories-categories/{categoryId}
     *
     * @param $categoryId
     * @return JsonResponse
     * @throws HttpException
     * @throws LogicException
     */
    public function deleteCategoryAction(int $categoryId): JsonResponse
    {
        $this->getRepository(StoryCategory::class)->delete($this->findEntity(Story::class, $categoryId));
        return $this->getJsonResponseEmpty();
    }
}
