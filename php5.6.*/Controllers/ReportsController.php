<?php
/**
 * Example
 *
 * @author Rodion Chernyshov
 * @copyright  Copyright (c) 2015 Eastern Peak Software Inc. (http://easternpeak.com/)
 *
 */

namespace app\modules\front\controllers;

use app\modules\front\forms\ReportOrdersForm;
use app\modules\front\forms\ReportOrdersSummaryForm;
use app\modules\front\forms\ReportProfessionalForm;
use app\shared\models\OrderModel;
use app\shared\models\UserModel;
use app\shared\traits\CachePageTrait;
use app\shared\traits\FrontControllerTrait;
use yii\base\InvalidParamException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Class StatisticsController
 *
 * @package app\modules\front\controllers
 */
class ReportsController extends Controller
{
    use FrontControllerTrait, CachePageTrait;
    
    /**
     * @return mixed
     * @throws \Exception
     */
    public function actionProfessionals()
    {
        $dataProvider = null;
        $reportForm = new ReportProfessionalForm;
        if ($reportForm->load($this->getRequest()->queryParams) && $reportForm->validate()) {
            $dataProvider = $reportForm->getDataProvider();
        }
        return $this->render(
            'professionals',
            ['reportForm' => $reportForm, 'dataProvider' => $dataProvider]
        );
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function actionOrders()
    {
        $dataProvider = null;
        $reportForm = new ReportOrdersForm;
        if ($reportForm->load($this->getRequest()->queryParams) && $reportForm->validate()) {
            $dataProvider = $reportForm->getDataProvider();
        }
        return $this->render(
            'orders',
            ['reportForm' => $reportForm, 'dataProvider' => $dataProvider]
        );
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function actionOrdersSummary()
    {
        $dataProvider = null;
        $reportForm = new ReportOrdersSummaryForm;
        if ($reportForm->load($this->getRequest()->queryParams) && $reportForm->validate()) {
            $dataProvider = $reportForm->getDataProvider();
        }
        return $this->render(
            'summary',
            ['reportForm' => $reportForm, 'dataProvider' => $dataProvider]
        );
    }

    /**
     *
     * @return Response
     * @throws HttpException
     * @throws \Exception
     * @throws InvalidParamException
     */
    public function actionExportOrders()
    {
        $this->layout = false;
        $reportForm = new ReportOrdersForm;
        $reportForm->dateFrom = $this->getRequest()->get('dateFrom');
        $reportForm->dateTo = $this->getRequest()->get('dateTo');
        if ($reportForm->validate()) {
            $dataProvider = $reportForm->getDataProvider();
            $dataProvider->pagination = false;
        } else {
            return $this->redirect(['orders']);
        }
        return $this->render('export', ['dataProvider' => $dataProvider]);
    }

    /**
     *
     * @return Response
     * @throws HttpException
     * @throws \Exception
     * @throws InvalidParamException
     */
    public function actionExportOrdersSummary()
    {
        $this->layout = false;
        $reportForm = new ReportOrdersSummaryForm;
        $reportForm->dateFrom = $this->getRequest()->get('dateFrom');
        $reportForm->dateTo = $this->getRequest()->get('dateTo');
        $reportForm->customerId = $this->getRequest()->get('customerId');
        $reportForm->professionalId = $this->getRequest()->get('professionalId');
        $reportForm->statusId = $this->getRequest()->get('statusId');
        $reportForm->categoryId = $this->getRequest()->get('categoryId');
        if ($reportForm->validate()) {
            $dataProvider = $reportForm->getDataProvider();
            $dataProvider->pagination = false;
        } else {
            return $this->redirect(['orders-summary']);
        }
        return $this->render('export', ['dataProvider' => $dataProvider]);
    }

    /**
     *
     * @return Response
     * @throws HttpException
     * @throws \Exception
     * @throws InvalidParamException
     */
    public function actionExportProfessionals()
    {
        $this->layout = false;
        $reportForm = new ReportProfessionalForm();
        $reportForm->userId = $this->getRequest()->get('userId');
        $reportForm->dateFrom = $this->getRequest()->get('dateFrom');
        $reportForm->dateTo = $this->getRequest()->get('dateTo');
        if ($reportForm->validate()) {
            $dataProvider = $reportForm->getDataProvider();
        } else {
            return $this->redirect(['professionals']);
        }
        return $this->render('export', ['dataProvider' => $dataProvider]);
    }
}
