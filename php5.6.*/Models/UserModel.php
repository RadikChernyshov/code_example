<?php
/**
 * Example
 *
 * @author Rodion Chernyshov
 * @copyright  Copyright (c) 2015 Eastern Peak Software Inc. (http://easternpeak.com/)
 *
 */

namespace app\shared\models;

use app\components\db\tables\UserJobProfileTable;
use app\components\db\tables\UserLocationTable;
use app\components\db\tables\UserTable;
use app\components\location\LocationComponent;
use app\components\notification\NotificationComponent;
use app\modules\api\models\OAuthAccessTokenModel;
use app\modules\api\models\OAuthSessionModel;
use app\modules\api\models\UserDeviceModel;
use app\shared\forms\QuestionnaireForm;
use app\shared\forms\UploadImageForm;
use app\shared\helpers\DbSchemaDateHelper;
use app\shared\interfaces\ModelInterface;
use app\shared\interfaces\UserProfileStatusInterface;
use app\shared\models\users\LocationModel;
use app\shared\traits\ModelCacheTrait;
use app\shared\traits\ModelTrait;
use app\shared\validators\IsValidPhoneNumber;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\ModelEvent;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\StringHelper;
use yii\validators\EmailValidator;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;


/**
 * Class User
 *
 * @property integer                     $id
 * @property integer                     $role_id
 * @property string                      $name
 * @property string                      $email
 * @property string                      $phone
 * @property string                      $password
 * @property string                      $created_at
 * @property string                      $updated_at
 * @property string                      $logged_at
 * @property string                      $ban
 * @property OAuthSessionModel[]         $authSessions
 * @property OrderModel[]                $orderOrders
 * @property UserCreditCardModel[]       $creditcards
 * @property UserJobProfileModel         $profile
 * @property UserJobProfileRatingModel[] $userProfileRatings
 * @property UserRoleModel               $role
 * @property UserDeviceModel[]           $userDevices
 * @property LocationModel               $location
 * @package app\common\models
 */
class UserModel extends ActiveRecord implements IdentityInterface, ModelInterface
{
    use ModelTrait, ModelCacheTrait;

    /**
     * @var MediaModel
     */
    public $media;
    /**
     * @var boolean
     */
    public $is_advertisement_accept;
    /**
     * @var integer
     */
    public $birth_date;
    /**
     * @var string
     */
    protected $authKey;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->on(
            self::EVENT_AFTER_USER_DELETE,
            [OAuthSessionModel::className(), 'clearUsersSessions']
        );
        $this->on(
            ModelInterface::EVENT_SEND_EMAIL_NOTIFICATION,
            [NotificationComponent::className(), 'eventNotification']
        );
    }

    /**
     * Get UserModel table name
     *
     * @return string
     */
    public static function tableName()
    {
        return UserTable::getTitle();
    }

    /**
     * Validation rules
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'email', 'phone', 'birth_date'], 'trim'],
            [['name', 'email', 'phone', 'birth_date'], 'default'],
            [['is_advertisement_accept'], 'boolean'],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['birth_date'], 'integer'],
            [['email'], 'isCanChangeEmail', 'on' => ModelInterface::SCENARIO_UPDATE],
            [['phone'], IsValidPhoneNumber::className()],
            [
                ['name'],
                'string',
                'min' => self::STRING_MIN_LENGTH,
                'max' => self::STRING_MAX_LENGTH
            ],
            [
                ['password'],
                'string',
                'min' => 6,
                'max' => self::STRING_MAX_LENGTH
            ],
            [['created_at', 'updated_at', 'logged_at', 'ban'], 'safe'],
            [
                ['name', 'email', 'phone', 'password'],
                'string',
                'max' => self::STRING_MAX_LENGTH
            ],
            [
                ['role_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => UserRoleModel::className(),
                'targetAttribute' => ['role_id' => 'id']
            ],
            [
                ['name', 'email', 'password'],
                'required',
                'on' => ModelInterface::SCENARIO_CREATE
            ],
            [
                ['name'],
                'required',
                'on' => ModelInterface::SCENARIO_SOCIAL_CREATE
            ],
            [
                ['name', 'email', 'phone', 'password'],
                'required',
                'on' => ModelInterface::SCENARIO_CUSTOM_CREATE
            ],
            [
                ['email', 'name', 'phone'],
                'required',
                'on' => ModelInterface::SCENARIO_UPDATE
            ],
            [
                ['email'],
                'required',
                'on' => ModelInterface::SCENARIO_RESET_PASSWORD
            ],
            [
                ['role_id'],
                'default',
                'value' => UserRoleModel::ROLE_CUSTOMER_ID,
                'on'    => ModelInterface::SCENARIO_CREATE
            ],
            [
                ['role_id'],
                'default',
                'value' => UserRoleModel::ROLE_CUSTOMER_ID,
                'on'    => ModelInterface::SCENARIO_SOCIAL_CREATE
            ],
            [
                ['password'],
                'required',
                'on' => ModelInterface::SCENARIO_SET_NEW_PASSWORD
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('view', 'ID'),
            'role_id' => Yii::t('view', 'Role'),
            'name' => Yii::t('view', 'Name'),
            'email' => Yii::t('view', 'Email'),
            'phone' => Yii::t('view', 'Phone'),
            'password' => Yii::t('view', 'Password'),
            'created_at' => Yii::t('view', 'Created'),
            'updated_at' => Yii::t('view', 'Updated'),
            'ban' => Yii::t('view', 'Ban'),
            'logged_at' => Yii::t('view', 'Last Logged At'),
            'media' => \Yii::t('view', 'Media'),
            'is_advertisement_accept' => \Yii::t('view', 'Is Advertisement Accept'),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return self|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function findIdentity($id)
    {
        return static::loadById($id);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(LocationModel::className(), ['user_id' => 'id'])
            ->with(LocationModel::getRelations());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDevices()
    {
        return $this->hasMany(UserDeviceModel::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(UserRoleModel::className(), ['id' => 'role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(UserJobProfileModel::className(), ['user_id' => 'id'])
            ->with(UserJobProfileModel::getRelations());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(OrderModel::className(), ['user_id' => 'id'])
            ->with(OrderModel::getRelations());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreditcards()
    {
        return $this->hasMany(UserCreditCardModel::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfileRatings()
    {
        return $this->hasMany(UserJobProfileRatingModel::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredentials()
    {
        return $this->hasMany(UserCredentialModel::className(), ['user_id' => 'id']);
    }

    /**
     * @return UserJobProfileModel
     */
    public function getProfileModel()
    {
        return $this->profile;
    }

    /**
     * @inheritDoc
     */
    public static function getRelations()
    {
        return ['location', 'profile', 'role'];
    }

    /**
     * @param string $attribute
     */
    public function isCanChangeEmail($attribute) {
        $paymentModels = OrderPaymentModel::getCurrentUserPayment($this);
        if($paymentModels) {
            $this->addError($attribute, \Yii::t('view', 'You can not change email address within active order'));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return null|static
     * @throws \yii\base\InvalidConfigException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        \Yii::$app->get('oauth')->getResourceServer()->isValidRequest(false, $token);
        $tokenModel = OAuthAccessTokenModel::loadByParam('token', $token);
        if($tokenModel) {
            $sessionModel = $tokenModel->getSessionModel();
            return static::findIdentity($sessionModel->getUserModel()->getId());
        }
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return md5($this->password);
    }

    /**
     * @param string $authKey
     *
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * {@inheritdoc}
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'logged_at' => function (self $userModel) {
                return $userModel->logged_at ? strtotime($userModel->logged_at) : null;
            },
            'profile' => function (self $userModel) {
                return $userModel->getProfileModel();
            },
            'creditcards',
            'creditcard' => function (self $userModel) {
                return UserCreditCardModel::find()
                    ->where([
                        'user_id' => $userModel->getId(),
                        'default' => true
                    ])
                    ->one();
            },
            'full_name' => function (self $userModel) {
                return $userModel->name;
            },
            'last_location' => function (self $userModel) {
                return $userModel->getLastLocation();
            }
        ];
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function extraFields()
    {
        return ['role', 'credentials'];
    }

    /**
     * @return array|mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getLastLocation()
    {
        $cacheKey = LocationComponent::getMasterLastLocationCacheKey($this->getId());
        $location = [
            'longitude' => 0,
            'latitude'  => 0
        ];
        if (self::getCache()->exists($cacheKey)) {
            $location = self::getCache()->get($cacheKey);
        } elseif($this->location) {
            $location = [
                'longitude' => $this->location->longitude,
                'latitude'  => $this->location->latitude
            ];
        }
        return $location;
    }

    /**
     * @param bool $insert
     *
     * @throws Exception
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->email = strtolower($this->email);
            if (ModelInterface::SCENARIO_CREATE === $this->scenario) {
                $this->password = $this->hashPassword($this->password);
                $this->authKey = $this->password;
            } elseif (ModelInterface::SCENARIO_UPDATE === $this->scenario) {
                if ($this->password && ($this->oldAttributes['password'] !== $this->password)) {
                    $this->password = $this->hashPassword($this->password);
                } else {
                    $this->password = $this->oldAttributes['password'];
                }
            } elseif (ModelInterface::SCENARIO_SET_NEW_PASSWORD === $this->scenario) {
                $this->password = $this->hashPassword($this->password);
            } elseif (ModelInterface::SCENARIO_CUSTOM_CREATE === $this->scenario) {
                $this->role_id = UserRoleModel::ROLE_MASTER_ID;
                $this->password = $this->hashPassword($this->password);
            }
            if (!$this->isNewRecord) {
                $this->updated_at = new Expression('NOW()');
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $password
     *
     * @return bool
     * @throws \yii\base\InvalidParamException
     */
    public function validatePassword($password = '')
    {
        return password_verify($password, $this->password);
    }

    /**
     * @param string $password
     *
     * @return bool|string
     * @throws \yii\base\Exception
     */
    protected function hashPassword($password = '')
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param bool  $insert
     * @param array $changedAttributes
     *
     * @throws InvalidParamException
     * @throws \yii\base\InvalidCallException
     * @throws \ErrorException
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     * @throws \yii\base\InvalidConfigException
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!$this->hasProfile()) {
            (new UserJobProfileModel)->createProfile($this);
            $this->refresh();
            $mediaInstance = UploadedFile::getInstance($this, 'media');
        } else {
            if(null !== $this->is_advertisement_accept) {
                $this->getProfileModel()->is_advertisement_accept = (bool)$this->is_advertisement_accept;
                $this->getProfileModel()->save(true, ['is_advertisement_accept']);
            }
            $mediaInstance = UploadedFile::getInstance($this->profile, 'media');
        }
        if($mediaInstance) {
            $this->setMedia($mediaInstance);
        }
        if ((ModelInterface::SCENARIO_CREATE === $this->getScenario())
            && (UserRoleModel::ROLE_CUSTOMER_ID === $this->role->getId())
        ) {
            $this->trigger(ModelInterface::EVENT_SEND_EMAIL_NOTIFICATION, new ModelEvent);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param int $daysToBan
     *
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @return bool
     */
    public function banUser($daysToBan = 0)
    {
        $daysToBan = (0 < $daysToBan) ? $daysToBan : 1000;
        $unbanDate = strtotime("+{$daysToBan} days");
        $this->ban = DbSchemaDateHelper::get($unbanDate);
        $isBanned = $this->save();
        if ($isBanned) {
            $this->profile->updateStatus(UserProfileStatusInterface::STATUS_BANNED);
        }
        return $isBanned;
    }

    /**
     * @return bool
     */
    public function unbanUser()
    {
        $this->ban = '';
        $isUnbanned = $this->save();
        if ($isUnbanned) {
            $this->profile->updateStatus(UserProfileStatusInterface::STATUS_APPROVED);
        }
        return $isUnbanned;
    }

    /**
     * @return bool
     */
    public function isMaster()
    {
        return ($this->role_id === UserRoleModel::ROLE_MASTER_ID);
    }

    /**
     * Check User Role
     *
     * @return bool
     */
    public function isCustomer()
    {
        return (UserRoleModel::ROLE_CUSTOMER_ID === $this->role_id);
    }

    /**
     * @return bool
     */
    public function isBanned()
    {
        return ((null !== $this->ban)
            && (UserProfileStatusInterface::STATUS_BANNED === $this->profile->status_id));
    }

    /**
     * @return bool
     */
    public function hasProfile()
    {
        return !(null === $this->profile);
    }

    /**
     * @return ActiveQuery
     */
    public function getAllUsersQuery() {
        $userRoleQuery = UserRoleModel::getRolesIdQueryById([UserRoleModel::ROLE_ADMINISTRATOR_ID]);
        return static::find()->where(['!=', 'role_id', $userRoleQuery]);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdministratorsQuery()
    {
        $userRoleQuery = UserRoleModel::getRolesIdQueryById([UserRoleModel::ROLE_ADMINISTRATOR_ID]);
        return static::find()
            ->where(['=', 'role_id', $userRoleQuery]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomersQuery()
    {
        $userRoleQuery = UserRoleModel::getRolesIdQueryById([UserRoleModel::ROLE_CUSTOMER_ID]);
        return static::find()->where(['=', 'role_id', $userRoleQuery]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBannedUsersQuery()
    {
        return static::find()->where('ban IS NOT NULL');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMastersQuery()
    {
        $userRoleQuery = UserRoleModel::getRolesIdQueryById([UserRoleModel::ROLE_MASTER_ID]);
        $activeQuery = static::find();
        $activeQuery->join(
            'INNER JOIN',
            UserJobProfileTable::getTitle(),
            UserTable::getTitle() . '.id = ' . UserJobProfileTable::getTitle() . '.user_id'
        );
        $activeQuery->where(['=', 'role_id', $userRoleQuery]);
        $activeQuery->andWhere([
            '=',
            UserJobProfileTable::getTitle() . '.status_id',
            UserProfileStatusInterface::STATUS_APPROVED
        ]);
        return $activeQuery;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMastersOnlineQuery()
    {
        $activeQuery = $this->getMastersQuery();
        $activeQuery->join(
            'INNER JOIN',
            UserLocationTable::getTitle(),
            UserTable::getTitle() . '.id = ' . UserLocationTable::getTitle() . '.user_id'
        );
        $activeQuery->andWhere(
            UserLocationTable::getTitle() .'.created_at > NOW() - INTERVAL \''. LocationModel::EXPIRATION_TIME .' seconds\'
            OR ' . UserLocationTable::getTitle() . '.updated_at > NOW() - INTERVAL \''. LocationModel::EXPIRATION_TIME .' seconds\''
        );
        return $activeQuery;
    }

    /**
     * @return boolean
     */
    public function isOnline()
    {
        $activeQuery = static::find();
        $activeQuery->join(
            'INNER JOIN',
            UserLocationTable::getTitle(),
            UserTable::getTitle() . '.id = ' . UserLocationTable::getTitle() . '.user_id'
        );
        $activeQuery->where(
            UserLocationTable::getTitle() .'.created_at > NOW() - INTERVAL \''. LocationModel::EXPIRATION_TIME .' seconds\'
            OR ' . UserLocationTable::getTitle() . '.updated_at > NOW() - INTERVAL \''. LocationModel::EXPIRATION_TIME .' seconds\''
        )->andWhere('user_id = :user_id', [':user_id' => $this->getId()]);
        return $activeQuery->exists();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPendingMastersQuery()
    {
        $userRoleQuery = UserRoleModel::getRolesIdQueryById([UserRoleModel::ROLE_MASTER_ID]);
        $activeQuery = static::find();
        $activeQuery->join(
            'INNER JOIN',
            UserJobProfileTable::getTitle(),
            UserTable::getTitle() . '.id = ' . UserJobProfileTable::getTitle() . '.user_id'
        );
        $activeQuery->where(['=', 'role_id', $userRoleQuery]);
        $activeQuery->andWhere([
                '=',
                UserJobProfileTable::getTitle() . '.status_id',
                UserProfileStatusInterface::STATUS_REGISTERED
            ]);
        $activeQuery->orderBy('created_at ASC');
        return $activeQuery;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotApprovedMastersQuery()
    {
        $activeQuery = static::find();
        $activeQuery->join(
            'INNER JOIN',
            UserJobProfileTable::getTitle(),
            UserTable::getTitle() . '.id = ' . UserJobProfileTable::getTitle() . '.user_id'
        )->where(
            ['=', 'role_id', UserRoleModel::ROLE_MASTER_ID]
        )->andWhere(
            UserJobProfileTable::getTitle() . '.status_id = :status_id',
            [':status_id' => UserProfileStatusInterface::STATUS_NOT_APPROVED]
        )->orderBy('created_at ASC');
        return $activeQuery;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public function getProfessions()
    {
        return MalfunctionCategoryModel::loadList();
    }

    /**
     * @param $mediaInstance
     *
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidCallException
     * @throws \ErrorException
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     */
    protected function setMedia($mediaInstance = null)
    {
        if ($mediaInstance) {
            $uploadImageForm = new UploadImageForm();
            $uploadImageForm->setFile($mediaInstance);
            $uploadImageForm->setInstance($this);
            $uploadImageForm->setPath(MediaModel::getUsersMediaPath());
            if ($uploadImageForm->upload()) {
                $mediaModel = new MediaModel;
                $mediaModel->setAttributes([
                    'type' => $uploadImageForm->file->type,
                    'path' => $uploadImageForm->getUserUploadedFileUrl()
                ]);
                $mediaModel->proceed($uploadImageForm);
                if ($mediaModel->save()) {
                    $this->refresh();
                    $this->getProfileModel()->link('media', $mediaModel);
                }
            } else {
                $this->addError('media', $uploadImageForm->getFirstError('file'));
            }
        }
    }

    /**
     * @param $email
     *
     * @return null|UserModel
     * @throws \yii\base\InvalidParamException
     */
    public static function findByEmail($email)
    {
        $validator = new EmailValidator;
        if (!$validator->validate($email)) {
            throw new InvalidParamException(
                \Yii::t(
                    'exception',
                    'E-mail "{email}" address is invalid',
                    ['email' => $email]
                )
            );
        }
        return static::find()
            ->where('email = LOWER(:email)', [':email' => strtolower(trim($email))])
            ->one();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->trigger(self::EVENT_AFTER_USER_DELETE, new ModelEvent);
    }
}
