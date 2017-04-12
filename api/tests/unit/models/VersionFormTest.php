<?php
namespace api\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\User;
use common\models\Version;
use api\models\VersionForm;

/**
 * VersionForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionFormTest extends \Codeception\Test\Unit
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
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/version.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);
    }

    /**
     * `VersionForm::validateUserProjectId()` method test.
     */
    public function testValidateUserProjectId()
    {
        $user = User::findOne(1002);

        $this->specify('Wrong project id attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'projectId' => 1002,
            ]);

            $model->validateUserProjectId('projectId', []);

            verify('Error message should be set', $model->errors)->hasKey('projectId');
        });

        $this->specify('Correct project id attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'projectId' => 1001,
            ]);

            $model->validateUserProjectId('projectId', []);

            verify('Error message should not be set', $model->errors)->hasntKey('projectId');
        });
    }

    /**
     * Tests whether `VersionForm::save()` creates a valid Version model.
     */
    public function testSaveCreate()
    {
        $user = User::findOne(1002);

        $this->specify('Error create attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'projectId' => 1002,
            ]);
            $result = $model->save();

            verify('Model should not save', $result)->null();
            verify('Error message should be set', $model->errors)->hasKey('projectId');
        });

        $this->specify('Success create attempt', function() use ($user) {
            $model = new VersionForm($user, [
                'projectId' => 1001,
            ]);
            $result = $model->save();

            verify('Model should save', $result)->isInstanceOf(Version::className());
            verify('Model should not have any errors', $model->errors)->isEmpty();
            verify('Version projectId should match', $result->projectId)->equals(1001);
        });
    }
}
