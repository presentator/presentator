<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\ProjectPreviewFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\Project;
use app\models\ProjectAccessForm;

/**
 * ProjectAccessForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectAccessFormTest extends \Codeception\Test\Unit
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
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project.php'),
            ],
            'preview' => [
                'class'    => ProjectPreviewFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/project_preview.php'),
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => Yii::getAlias('@common/tests/_data/user_project_rel.php'),
            ],
        ]);
    }

    /**
     * `ProjectAccessForm::validatePassword()` method test.
     */
    public function testValidatePassword()
    {

        $this->specify('Error validate attempt for PROTECTED project', function() {
            $project = Project::findOne(1002);
            $model = new ProjectAccessForm($project, [
                'password' => 'someInvalidPassword',
            ]);
            $model->validatePassword('password', []);

            verify('Error message should be set', $model->errors)->hasKey('password');
        });

        $this->specify('Correct validate attempt for PROTECTED project', function() {
            $project = Project::findOne(1002);
            $model = new ProjectAccessForm($project, [
                'password' => '123456',
            ]);
            $model->validatePassword('password', []);

            verify('Error message should not be set', $model->errors)->hasntKey('password');
        });

        $this->specify('Correct validate attempt for UNPROTECTED project', function() {
            $project = Project::findOne(1001);
            $model = new ProjectAccessForm($project);
            $model->validatePassword('password', []);

            verify('Error message should not be set', $model->errors)->hasntKey('password');
        });
    }

    /**
     * `ProjectAccessForm::grantAccess()` method test.
     */
    public function testGrantAccess()
    {
        $this->specify('Access declined', function() {
            $project = Project::findOne(1002);
            $model = new ProjectAccessForm($project, [
                'password' => 'someInvalidPassword',
            ]);

            verify('Access should not be granted', $model->grantAccess())->false();
            verify('Error message should be set', $model->errors)->hasKey('password');
        });

        $this->specify('Access granted', function() {
            $project = Project::findOne(1002);
            $model = new ProjectAccessForm($project, [
                'password' => '123456',
            ]);

            verify('Access should be granted', $model->grantAccess())->true();
            verify('Error message should not be set', $model->errors)->hasntKey('password');
        });
    }
}
