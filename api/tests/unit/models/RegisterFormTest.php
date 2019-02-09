<?php
namespace api\tests\models;

use Yii;
use yii\helpers\Html;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;
use api\models\RegisterForm;

/**
 * RegisterForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class RegisterFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \api\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user.php'),
            ],
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_setting.php'),
            ],
        ]);
    }

    /**
     * `RegisterForm::register()` method test.
     */
    public function testRegister()
    {
        $this->specify('Error register attemp', function() {
            $imagePath = Yii::getAlias('@common/tests/_data/test_image.gif'); // unsupported type

            $model = new RegisterForm([
                'email'           => 'invalid_email@',
                'password'        => '123',
                'passwordConfirm' => '12345',
                'firstName'       => 'Test',
                'lastName'        => '',
                'avatar'          => $this->tester->getUploadedFileInstance($imagePath),
                'notifications'   => -10,
            ]);

            $result = $model->register();

            verify('Model should not validate', $result)->null();
            verify('email error message should be set', $model->errors)->hasKey('email');
            verify('password error message should be set', $model->errors)->hasKey('password');
            verify('passwordConfirm error message should be set', $model->errors)->hasKey('passwordConfirm');
            verify('avatar error message should be set', $model->errors)->hasKey('avatar');
            verify('notifications error message should be set', $model->errors)->hasKey('notifications');
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Success update attempt', function() {
            $imagePath = Yii::getAlias('@common/tests/_data/test_image.png'); // supported type

            $model = new RegisterForm([
                'email'           => 'test123@presentator.io',
                'password'        => '123456',
                'passwordConfirm' => '123456',
                'firstName'       => 'Test',
                'lastName'        => '',
                'avatar'          => $this->tester->getUploadedFileInstance($imagePath),
                'notifications'   => false,
            ]);

            $result = $model->register();

            verify('Model should not has any errors', $model->errors)->isEmpty();
            verify('Model should return instance of User', $result)->isInstanceOf(User::className());
            verify('Email should match', $result->email)->equals('test123@presentator.io');
            verify('First name should match', $result->firstName)->equals('Test');
            verify('Last name should match', $result->lastName)->equals('');
            verify('Notification setting should match', $result->getSetting(User::NOTIFICATIONS_SETTING_KEY))->equals(false);
            verify('Password should match', $result->validatePassword('123456'))->true();
            $this->tester->seeEmailIsSent();
            $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
            verify('Body should contains an activation url', current($message->getChildren())->getBody())->contains(
                Html::encode(Yii::$app->mainUrlManager->createUrl(['site/activation', 'email' => $result->email, 'token' => $result->getActivationToken()], true))
            );
        });
    }
}
