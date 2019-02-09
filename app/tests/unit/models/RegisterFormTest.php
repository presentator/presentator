<?php
namespace app\tests\models;

use Yii;
use yii\helpers\Html;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\models\User;
use common\models\UserSetting;
use app\models\RegisterForm;

/**
 * RegisterForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class RegisterFormTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \app\tests\UnitTester
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
     * `RegisterForm:register()` method test.
     */
    public function testRegister()
    {
        $this->specify('Wrong register attempt', function() {
            $model = new RegisterForm([
                'email'           => 'invalid_email',
                'password'        => '123',
                'passwordConfirm' => '123456',
                'terms'           => false,
            ]);

            verify('Model should not register', $model->register())->null();
            verify('Email error message should be set', $model->errors)->hasKey('email');
            verify('Password error message should be set', $model->errors)->hasKey('password');
            verify('PasswordConfirm error message should be set', $model->errors)->hasKey('passwordConfirm');
            verify('Terms error message should be set', $model->errors)->hasKey('terms');

            // activation email should not be sent
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Correct register attempt', function() {
            $model = new RegisterForm([
                'email'           => 'valid_email@presentator.io',
                'password'        => '123456',
                'passwordConfirm' => '123456',
                'terms'           => true,
            ]);

            $result = $model->register();

            verify('No error messages should be set', $model->errors)->isEmpty();
            verify('Model should register', $result)->isInstanceOf(User::className());
            verify('User should be inactive', $result->status)->equals(User::STATUS_INACTIVE);
            verify('User email should be set', $result->email)->equals('valid_email@presentator.io');
            verify('User passwordHash should be set', $result->passwordHash)->notEmpty();

            // activation email should be sent
            $this->tester->seeEmailIsSent();
            $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
            verify('"To" email should match', $message->getTo())->hasKey('valid_email@presentator.io');
            verify('Body should contains an activation url.', current($message->getChildren())->getBody())->contains(
                Html::encode(Yii::$app->mainUrlManager->createUrl(['site/activation', 'email' => $result->email, 'token' => $result->getActivationToken()], true))
            );
        });
    }
}
