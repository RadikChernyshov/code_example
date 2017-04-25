<?php
/**
 * Example
 *
 * @author Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 *
 */

namespace app\modules\front\controllers;

use app\shared\models\LanguageModel;
use app\shared\models\NotificationModel;
use app\shared\traits\FrontControllerTrait;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class NotificationsController
 *
 * @package app\modules\front\controllers
 */
class NotificationsController extends Controller
{
    use FrontControllerTrait;

    /**
     * @throws InvalidParamException
     * @throws \Exception
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = $this->getProvider(NotificationModel::find(), ['created_at' => SORT_DESC]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * @return mixed
     * @throws InvalidParamException
     * @throws \Exception
     * @throws InvalidConfigException
     */
    public function actionAdd()
    {
        $notificationModel = new NotificationModel;
        $notificationModel->loadDefaultValues();
        if ($this->isPost() && $notificationModel->load($this->getPost()) && $notificationModel->save()) {
            $this->setSuccessFlash(\Yii::t('view', 'The notification has been added to the queue.'));
            return $this->redirect(['index']);
        }
        return $this->render(
            'add',
            [
                'notificationModel'  => $notificationModel,
                'languageCollection' => LanguageModel::getLanguages()
            ]
        );
    }

    /**
     * @param int $notificationId
     *
     * @return mixed|Response
     * @throws \Exception
     * @throws \yii\web\HttpException
     * @throws InvalidParamException
     * @throws InvalidConfigException
     */
    public function actionEdit($notificationId)
    {
        $notificationModel = $this->loadModel(NotificationModel::className(), $notificationId);
        if ($this->isPost() && $notificationModel->load($this->getPost()) && $notificationModel->save()) {
            $this->setSuccessFlash(\Yii::t('view', 'The notification has been updated.'));
            return $this->redirect(['index']);
        }
        return $this->render(
            'edit',
            [
                'notificationModel'  => $notificationModel,
                'languageCollection' => LanguageModel::getLanguages()
            ]
        );
    }

    /**
     * @param int $notificationId
     *
     * @return Response
     * @throws \Exception
     * @throws \yii\web\HttpException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($notificationId)
    {
        $notificationModel = $this->loadModel(NotificationModel::className(), $notificationId);
        if ($notificationModel->delete()) {
            $this->setSuccessFlash(\Yii::t('view', 'The notification has been deleted.'));
        } else {
            $this->setDangerFlash(\Yii::t('view', 'Can\'t delete the notification.'));
        }
        return $this->redirect(['index']);
    }
}
