<?php
/**
 * Example
 *
 * @author Rodion Chernyshov
 * @copyright  Copyright (c) 2017
 */

namespace app\shared\models;

use app\components\db\tables\MalfunctionTable;
use app\shared\forms\SearchForm;
use app\shared\helpers\ArrayConvertHelper;
use app\shared\interfaces\ModelInterface;
use app\shared\interfaces\ModelSearchInterface;
use app\shared\traits\ModelCurrentUserTrait;
use app\shared\traits\ModelLanguageTrait;
use app\shared\traits\ModelMediaTrait;
use app\shared\traits\ModelTrait;
use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\UploadedFile;

/**
 * Class MalfunctionModel
 *
 * @property integer                  $id
 * @property integer                  $category_id
 * @property integer                  $media_id
 * @property string                   $internal_id
 * @property string                   $amount
 * @property string                   $title
 * @property string                   $description
 * @property string                   $created_at
 * @property string                   $updated_at
 * @property boolean                  $is_active
 * @property boolean                  $is_promoted
 *
 * @property MalfunctionCategoryModel $category
 * @property MediaModel               $media
 * @property OrderModel[]             $orderOrders
 * @package app\shared\models
 */
class MalfunctionModel extends ActiveRecord implements ModelInterface, ModelSearchInterface
{
    use ModelTrait, ModelMediaTrait, ModelLanguageTrait, ModelCurrentUserTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return MalfunctionTable::getTitle();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'media_id', 'is_promoted'], 'integer'],
            [['category_id', 'internal_id'], 'required'],
            [['amount'], 'number'],
            [['is_active'], 'boolean'],
            [['title', 'description'], 'isValidMultiLanguage'],
            [['created_at', 'updated_at'], 'safe'],
            [
                ['category_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => MalfunctionCategoryModel::className(),
                'targetAttribute' => ['category_id' => 'id']
            ],
            [
                ['media_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => MediaModel::className(),
                'targetAttribute' => ['media_id' => 'id']
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('view', 'ID'),
            'category_id' => Yii::t('view', 'Category'),
            'internal_id' => Yii::t('view', 'Catalog Number'),
            'media_id'    => Yii::t('view', 'Media'),
            'amount'      => Yii::t('view', 'Amount'),
            'title'       => Yii::t('view', 'Title'),
            'description' => Yii::t('view', 'Description'),
            'created_at'  => Yii::t('view', 'Created At'),
            'updated_at'  => Yii::t('view', 'Updated At'),
            'is_active'   => Yii::t('view', 'Is Active'),
            'is_promoted' => Yii::t('view', 'Is Promoted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(
            MalfunctionCategoryModel::className(),
            ['id' => 'category_id']
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedia()
    {
        return $this->hasOne(MediaModel::className(), ['id' => 'media_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasMany(OrderModel::className(), ['category_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     */
    public function fields()
    {
        return [
            'id', 'internal_id',
            'title' => function (self $malfunctionModel) {
                return $malfunctionModel->getTranslatedColumnByLang(
                    'title',
                    $this->getCurrentLanguageCode()
                );
            },
            'description' => function (self $malfunctionModel) {
                return $malfunctionModel->getTranslatedColumnByLang(
                    'description',
                    $this->getCurrentLanguageCode()
                );
            },
            'category_title' => function (self $malfunctionModel) {
                return $malfunctionModel->category->getTranslatedColumnByLang(
                    'title',
                    $this->getCurrentLanguageCode()
                );
            },
            'amount' => function (self $malfunctionModel) {
                return $malfunctionModel->amount;
            }
        ];
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return ['category'];
    }

    /**
     * @param SearchForm $form
     *
     * @return \yii\db\ActiveQuery
     */
    public function search(SearchForm $form)
    {
        $currentLanguageCode = $this->getCurrentLanguageCode();
        return static::find()->where("LOWER(title->'{$currentLanguageCode}') LIKE LOWER('%{$form->s}%')");
    }

    /**
     * @param bool $insert
     *
     * @return bool
     * @throws \ErrorException
     * @throws InvalidParamException
     */
    public function beforeSave($insert)
    {
        $mediaInstance = UploadedFile::getInstance($this, 'media');
        if ($mediaInstance) {
            $this->setMedia($this, $mediaInstance);
        }
        if (is_array($this->title)) {
            $this->title = ArrayConvertHelper::hstoreEncode($this->title);
        }
        if (is_array($this->description)) {
            $this->description = ArrayConvertHelper::hstoreEncode($this->description);
        }
        if (!$this->isNewRecord) {
            $this->updated_at = new Expression('NOW()');
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (is_string($this->title)) {
            $this->title = ArrayConvertHelper::hstoreDecode($this->title);
        }
        if (is_string($this->description)) {
            $this->description = ArrayConvertHelper::hstoreDecode($this->description);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     */
    public function afterFind()
    {
        if (is_string($this->title)) {
            $this->title = ArrayConvertHelper::hstoreDecode($this->title);
        }
        if (is_string($this->description)) {
            $this->description = ArrayConvertHelper::hstoreDecode($this->description);
        }
        parent::afterFind();
    }

    /**
     * @param \app\shared\models\MalfunctionCategoryModel $categoryModel
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActive(MalfunctionCategoryModel $categoryModel)
    {
        return static::find()
            ->where([
                'is_active' => true,
                'category_id' => $categoryModel->getId()
            ])
            ->orderBy(['internal_id' => SORT_ASC]);
    }

    /**
     * @param MalfunctionCategoryModel $categoryModel
     *
     * @return array|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public static function getActiveList(MalfunctionCategoryModel $categoryModel
    ) {
        return \Yii::$app->db->cache(function () use ($categoryModel) {
            return static::find()
                ->where([
                    'is_active' => true,
                    'category_id' => $categoryModel->getId()
                ])
                ->orderBy(['id' => SORT_ASC])
                ->all();
        });
    }

    /**
     * @param int $isPromoted
     *
     * @return bool
     */
    public function setPromotedState($isPromoted)
    {
        $this->is_promoted = (int)$isPromoted;
        return $this->save();
    }
}
