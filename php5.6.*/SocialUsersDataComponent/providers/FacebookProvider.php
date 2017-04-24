<?php
/**
 * Example
 *
 * @author     Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

namespace app\modules\api\components\social\providers;

use app\modules\api\components\social\interfaces\SocialProviderInterface;
use app\modules\api\forms\SocialForm;
use app\shared\interfaces\ModelInterface;
use app\shared\models\UserModel;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphUser;

/**
 * Class FacebookProvider
 *
 * @package app\modules\api\components\social\providers
 */
class FacebookProvider extends AbstractProvider implements SocialProviderInterface
{
    /**
     * @const string
     */
    const PROVIDER_TYPE = 'facebook';
    /**
     * @const int
     */
    const PROVIDER_ID = 1;
    /**
     * @const string
     */
    const PROVIDER_APP_ID = '439626396232051';
    /**
     * @const string
     */
    const PROVIDER_APP_SECRET = '1f756cd67b5b3e9ff2333b49f73032c3';
    /**
     * @const string
     */
    const FACEBOOK_ENDPOINT_URI = '/me?fields=id,name,email,first_name,last_name';
    /**
     * @const string
     */
    const FACEBOOK_API_VERSION = 'v2.6';

    /**
     * FacebookProvider constructor.
     *
     * @param \app\modules\api\forms\SocialForm $form
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function __construct(SocialForm $form)
    {
        $this->form = $form;
        $this->userModel = new UserModel;
        $this->apiClient = new Facebook([
            'app_id' => self::PROVIDER_APP_ID,
            'app_secret' => self::PROVIDER_APP_SECRET,
            'default_graph_version' => self::FACEBOOK_API_VERSION
        ]);
    }

    /**
     * @throws FacebookSDKException
     * @throws \InvalidArgumentException
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function createUpdateUser()
    {
        $userData = $this->getUserData();
        $this->userModel->scenario = ModelInterface::SCENARIO_SOCIAL_CREATE;
        if (array_key_exists('email', $userData) && ('' !== $userData['email'])) {
            $existUserModel = UserModel::findOne(['email' => $userData['email']]);
            if ($existUserModel) {
                $this->userModel = $existUserModel;
            }
        }
        $this->userModel->load($userData, '');
        $isCreated = $this->userModel->save();
        if ($isCreated) {
            $this->createUsersCredentials($this->userModel, self::PROVIDER_ID, $userData['credential_id']);
            $this->updateUsersProfileMedia($this->userModel, $userData['media']);
        }
        return $isCreated;
    }

    /**
     * @throws \InvalidArgumentException
     * @throws FacebookSDKException
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getUserData()
    {
        $this->apiClient->setDefaultAccessToken($this->form->access_token);
        $endpointResponse = $this->apiClient->get(self::FACEBOOK_ENDPOINT_URI, $this->form->access_token);
        $usersDataNode = $endpointResponse->getGraphUser();
        return [
            'credential_id' => $usersDataNode->getId(),
            'name' => $this->getUsersFullName($usersDataNode),
            'media' => $this->getUsersMediaUrl($usersDataNode),
            'email' => $usersDataNode->getEmail()
        ];
    }

    /**
     * @param GraphUser $graphUser
     *
     * @return string
     */
    private function getUsersFullName(GraphUser $graphUser)
    {
        return trim($graphUser->getFirstName() . ' ' . $graphUser->getLastName());
    }

    /**
     * @param GraphUser $graphUser
     *
     * @return string
     */
    private function getUsersMediaUrl(GraphUser $graphUser)
    {
        return sprintf('https://graph.facebook.com/%d/picture?type=large', $graphUser->getId());
    }

    /**
     * @param UserModel $userModel
     */
    public function setUserModel($userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * @return bool
     */
    public function validateToken()
    {
        $tokenData = $this->apiClient->getOAuth2Client()->debugToken($this->form->access_token);
        if (!$tokenData->getIsValid()) {
            $this->form->addError(
                'access_token',
                \Yii::t('view', 'Token is invalid or expired.')
            );
        } else {
            $tokenScopes = $tokenData->getScopes();
            if (!in_array('email', $tokenScopes, true)) {
                $this->form->addError(
                    'access_token',
                    \Yii::t('view', 'Token scopes is invalid. Email does not exist.')
                );
            }
            if (!in_array('public_profile', $tokenScopes, true)) {
                $this->form->addError(
                    'access_token',
                    \Yii::t('view', 'Token scopes is invalid. Public Profile does not exist.')
                );
            }
        }
        return $this->form->hasErrors();
    }
}