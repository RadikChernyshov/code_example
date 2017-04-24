<?php
/**
 * Example
 *
 * @author     Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

namespace app\modules\api\components\social\providers;

use app\modules\api\forms\SocialForm;
use app\shared\models\MediaModel;
use app\shared\models\UserCredentialModel;
use app\shared\models\UserJobProfileModel;
use app\shared\models\UserModel;
use Facebook\Facebook;
use Google_Client;

/**
 * Class AbstractProvider
 *
 * @package app\modules\api\components\social\providers
 */
abstract class AbstractProvider
{
    /**
     * @var Google_Client|Facebook
     */
    protected $apiClient;
    /**
     * @var SocialForm
     */
    protected $form;
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @return UserModel
     */
    public function getUserModel()
    {
        return $this->userModel;
    }

    /**
     * @param UserModel $userModel
     * @param int|string $credentialId
     * @param string $credentialValue
     */
    public function createUsersCredentials(UserModel $userModel, $credentialId, $credentialValue = '')
    {
        $userCredential = UserCredentialModel::findOne(['credential_value' => $credentialValue]);
        if (!$userCredential) {
            $userCredential = new UserCredentialModel;
        }
        $userCredential->user_id = $userModel->getId();
        $userCredential->credential_id = (int)$credentialId;
        $userCredential->credential_value = $credentialValue;
        $userCredential->save();
    }

    /**
     * @param UserModel $userModel
     * @param string $mediaPath
     *
     * @return bool
     */
    public function updateUsersProfileMedia(UserModel $userModel, $mediaPath = '')
    {
        $mediaModel = new MediaModel;
        $mediaModel->type = MediaModel::AVATAR_SOCIAL_TYPE;
        $mediaModel->path = $mediaPath;
        $isMediaCreated = $mediaModel->save();
        if ($isMediaCreated) {
            $userProfile = UserJobProfileModel::findOne(['user_id' => $userModel->getId()]);
            $userProfile->media_id = $mediaModel->getId();
            $userProfile->save();
        }
        return $isMediaCreated;
    }
}
