<?php
/**
 * Example
 *
 * @author     Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

namespace app\modules\api\components\social;

use app\modules\api\components\social\interfaces\SocialProviderInterface;
use app\modules\api\components\social\providers\FacebookProvider;
use app\modules\api\components\social\providers\GoogleProvider;
use app\modules\api\forms\SocialForm;
use yii\base\InvalidParamException;

/**
 * Class SocialComponent
 *
 * @package app\modules\api\components\social
 */
class SocialComponent
{
    /**
     * @var SocialForm
     */
    protected $form;

    /**
     * @var SocialProviderInterface
     */
    protected $provider;

    /**
     * @return SocialForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param SocialForm $form
     *
     * @return $this
     */
    public function setForm(SocialForm $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @throws InvalidParamException;
     * @return SocialProviderInterface|FacebookProvider|GoogleProvider
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getProvider()
    {
        if ($this->form instanceof SocialForm) {
            $type = $this->form->type;
            if ($type === FacebookProvider::PROVIDER_TYPE) {
                $this->provider = new FacebookProvider($this->form);
            } elseif ($type === GoogleProvider::PROVIDER_TYPE) {
                $this->provider = new GoogleProvider($this->form);
            } else {
                throw new InvalidParamException(\Yii::t('exception', 'Invalid Provider Instance.'));
            }
        } else {
            throw new InvalidParamException(\Yii::t('exception', 'Invalid Form Instance.'));
        }
        return $this->provider;
    }
}