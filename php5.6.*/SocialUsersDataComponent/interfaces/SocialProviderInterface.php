<?php
/**
 * Example
 *
 * @author     Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

namespace app\modules\api\components\social\interfaces;

use app\shared\models\UserModel;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Interface SocialProviderInterface
 *
 * @package app\modules\api\components\social\interfaces
 */
interface SocialProviderInterface
{
    /**
     * @throws FacebookSDKException
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function createUpdateUser();

    /**
     * @return UserModel
     */
    public function getUserModel();

    /**
     * @return bool
     */
    public function validateToken();
}
