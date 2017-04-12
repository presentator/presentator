<?php
namespace api\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\models\User;
use api\models\LoginForm;

/**
 * LoginForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class LoginFormTest extends \Codeception\Test\Unit
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
        ]);
    }

    /**
     * `LoginForm::getUser()` method test.
     */
    public function testGetUser()
    {
        $this->specify('Try to get an INACTIVE user', function() {
            $model = new LoginForm([
                'email' => 'test1@presentator.io',
            ]);

            verify('User should not be found', $model->getUser())->null();
        });

        $this->specify('Try to get an ACTIVE user', function() {
            $model = new LoginForm([
                'email' => 'test2@presentator.io',
            ]);

            verify('User should be found', $model->getUser())->isInstanceOf(User::className());
        });
    }

    /**
     * `LoginForm::validatePassword()` method test.
     */
    public function testValidatePassword()
    {
        $this->specify('Wrong value attempt', function() {
            $model = new LoginForm([
                'email'    => 'test2@presentator.io',
                'password' => 'someInvalidPassword',
            ]);
            $model->validatePassword('password', []);

            verify('Error message should be set', $model->errors)->hasKey('password');
        });

        $this->specify('Correct value attempt', function() {
            $model = new LoginForm([
                'email'    => 'test2@presentator.io',
                'password' => '123456',
            ]);
            $model->validatePassword('password', []);

            verify('Error message should not be set', $model->errors)->hasntKey('password');
        });
    }

    /**
     * `LoginForm::login()` method test.
     */
    public function testLogin()
    {
        $this->specify('Try to login with an inactive user', function() {
            $model = new LoginForm([
                'email'    => 'test1@presentator.io',
                'password' => '123456',
            ]);

            verify('Model should not validate', $model->login())->false();
        });

        $this->specify('Try to login with an invalid password', function() {
            $model = new LoginForm([
                'email'    => 'test2@presentator.io',
                'password' => 'someInvalidPassword',
            ]);

            verify('Model should not validate', $model->login())->false();
            verify('Error message should be set', $model->errors)->hasKey('password');
            verify('Access response header should not be set', Yii::$app->response->headers->get('X-Access-Token'))->isEmpty();
        });

        $this->specify('Try to login with an active user and valid password', function() {
            $model = new LoginForm([
                'email'    => 'test2@presentator.io',
                'password' => '123456',
            ]);

            verify('Model should validate', $model->login())->true();
            verify('Error message should not be set', $model->errors)->hasntKey('password');
            verify('Access response header should be set', Yii::$app->response->headers->get('X-Access-Token'))->notEmpty();
        });
    }
}
