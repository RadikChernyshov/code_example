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
use Google_Client;

/**
 * Class GoogleProvider
 *
 * @property Google_Client $apiClient
 * @package app\modules\api\components\social\providers
 */
class GoogleProvider extends AbstractProvider implements SocialProviderInterface
{
    /**
     * @const string
     */
    const PROVIDER_TYPE = 'google';
    /**
     * @const int
     */
    const PROVIDER_ID = 2;
    /**
     * @const string
     */
    const GOOGLE_CLIENT_ID = '291535790630-qrar3evfr921cb8v8vq6d1h1gudkqq5i.apps.googleusercontent.com';
    /**
     * @const string
     */
    const GOOGLE_CLIENT_SECRET = 'ZxZd_UbFCxNkAB8hBEby9gmz';
    /**
     * @const string
     */
    const GOOGLE_ENDPOINT_URL = 'https://www.googleapis.com/oauth2/v1';

    /**
     * GoogleProvider constructor.
     *
     * @param \app\modules\api\forms\SocialForm $form
     */
    public function __construct(SocialForm $form)
    {
        $this->userModel = new UserModel;
        $this->form = $form;
        $this->initApiClient();
    }

    /**
     *
     */
    protected function initApiClient()
    {
        $this->apiClient = new Google_Client();
        $this->apiClient->setAccessType('online');
        $this->apiClient->setDeveloperKey('AIzaSyBN4O5k7FhBCT0NjzB5FTG7uSwgenBAoBU');
        $this->apiClient->setApplicationName('FixitJoeTest');
        $this->apiClient->setClientId(self::GOOGLE_CLIENT_ID);
        $this->apiClient->setClientSecret(self::GOOGLE_CLIENT_SECRET);
        $this->apiClient->setScopes(
            [
                'https://www.googleapis.com/auth/plus.me',
                'https://www.googleapis.com/auth/plus.login',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile'
            ]
        );
    }

    /**
     * @return bool
     * @throws \Google_Exception
     */
    public function createUpdateUser()
    {
        $isCreated = false;
        $this->userModel->scenario = ModelInterface::SCENARIO_SOCIAL_CREATE;
        $userData = $this->getUserData();
        if (array_key_exists('email', $userData)) {
            $existUserModel = UserModel::findOne(['email' => $userData]);
            if ($existUserModel) {
                $this->userModel = $existUserModel;
            }
        }
        $this->userModel->load($userData, '');
        if ($this->userModel->save()) {
            $this->createUsersCredentials($this->userModel, self::PROVIDER_ID, $userData['credential_id']);
            $this->updateUsersProfileMedia($this->userModel, $userData['media']);
            $isCreated = true;
        }
        return $isCreated;
    }

    /**
     * @return array
     * @throws \Google_Exception
     */
    private function getUserData()
    {
        $usersDataRequest = new \Google_Http_Request(
            sprintf(self::GOOGLE_ENDPOINT_URL . '/userinfo?access_token=%s', $this->form->access_token)
        );
        $usersData = $this->apiClient->execute($usersDataRequest);
        return [
            'credential_id' => $usersData['id'],
            'name' => $this->getUsersFullName($usersData),
            'media' => $usersData['picture'],
            'email' => $usersData['email']
        ];
    }

    /**
     * @param array $userData
     *
     * @return string
     */
    private function getUsersFullName($userData)
    {
        return trim($userData['given_name'] . ' ' . $userData['family_name']);
    }

    /**
     * @return bool
     * @throws \Google_Exception
     */
    public function validateToken()
    {
        $tokenRequest = new \Google_Http_Request(
            sprintf(self::GOOGLE_ENDPOINT_URL . '/tokeninfo?access_token=%s', $this->form->access_token)
        );
        $response = $this->apiClient->execute($tokenRequest);
        if (array_key_exists('error', $response)) {
            $this->form->addError('access_token', \Yii::t('view', 'Google token is invalid.'));
        }
    }
}