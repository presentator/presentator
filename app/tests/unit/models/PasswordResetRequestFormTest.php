<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use app\models\PasswordResetRequestForm;

/**
 * PasswordResetRequestForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PasswordResetRequestFormTest extends \Codeception\Test\Unit
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
        ]);
    }

    /**
     * `PasswordResetRequestForm::enquirePasswordReset()` method test.
     */
    public function testEnquirePasswordReset()
    {
        $this->specify('Reset request with INVALID email', function() {
            $model = new PasswordResetRequestForm([
                'email' => 'invalidEmail',
            ]);

            verify('Model should not validate', $model->enquirePasswordReset())->false();
            verify('Error message should be set', $model->errors)->hasKey('email');
        });

        $this->specify('Reset request with INACTIVE user email', function() {
            $model = new PasswordResetRequestForm([
                'email' => 'test1@presentator.io',
            ]);

            verify('Model should not validate', $model->enquirePasswordReset())->false();
            verify('Error message should be set', $model->errors)->hasKey('email');
        });

        $this->specify('Valid reset request', function() {
            $model = new PasswordResetRequestForm([
                'email' => 'test2@presentator.io',
            ]);

            verify('Model should validate', $model->enquirePasswordReset())->true();
            verify('Error message should not be set', $model->errors)->hasntKey('email');
        });

        $this->specify('Reset email is already send', function() {
            $model = new PasswordResetRequestForm([
                'email' => 'test4@presentator.io',
            ]);

            verify('Model should not validate', $model->enquirePasswordReset())->false();
            verify('Error message should not be set', $model->errors)->hasntKey('email');
            verify('Flash message should be set', Yii::$app->session->hasFlash('info'))->true();
        });
    }
}
