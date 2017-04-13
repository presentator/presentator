<?php
namespace api\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\components\helpers\CFileHelper;
use common\components\web\CUploadedFile;
use Imagine\Image\Box;
use yii\imagine\Image;

/**
 * API Register form model.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class RegisterForm extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $passwordConfirm;

    /**
     * @var string
     */
    public $firstName = '';

    /**
     * @var string
     */
    public $lastName = '';

    /**
     * @var boolean
     */
    public $notifications = true;

    /**
     * @var CUploadedFile
     */
    public $avatar = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password', 'passwordConfirm'], 'required'],
            ['email', 'email'],
            [['email', 'firstName', 'lastName'], 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::className()],
            ['password', 'string', 'min' => 4],
            ['passwordConfirm', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('app', "Passwords don't match")],
            ['notifications', 'boolean'],
            [['avatar'], 'image',
                'skipOnEmpty' => true,
                'extensions'  => 'png, jpg',
                'maxFiles'    => 1,
                'maxSize'     => (1024 * 1024 * Yii::$app->params['maxUploadSize']),
                'maxHeight'   => 3500,
                'maxWidth'    => 3500,
            ],
        ];
    }

    /**
     * Creates and return a new inactive user.
     * @return null|User
     */
    public function register()
    {
        $this->avatar = CUploadedFile::getInstanceByName('avatar');

        if ($this->validate()) {
            $user            = new User;
            $user->status    = User::STATUS_INACTIVE;
            $user->email     = $this->email;
            $user->firstName = $this->firstName;
            $user->lastName  = $this->lastName;
            $user->setPassword($this->password);

            if ($user->save() && $user->sendActivationEmail()) {
                // store avatar
                if ($this->avatar && $this->avatar instanceof CUploadedFile) {
                    CFileHelper::createDirectory($user->getUploadDir());

                    Image::getImagine()
                        ->open($this->avatar->tempName)
                        ->thumbnail(new Box(1000, 1000))
                        ->save($user->getAvatarPath(), ['quality' => 90]);

                    $user->cropAvatar();
                }

                // set user settings
                $user->setSetting(User::LANGUAGE_SETTING_KEY, Yii::$app->language);
                $user->setSetting(User::NOTIFICATIONS_SETTING_KEY, $this->notifications ? true : false);

                return $user;
            }
        }

        return null;
    }
}
