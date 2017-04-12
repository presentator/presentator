<?php
namespace common\tests\unit\models;

use yii\db\ActiveQuery;
use common\models\User;
use common\models\Project;
use common\models\UserProjectRel;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\UserProjectRelFixture;

/**
 * UserProjectRel AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserProjectRelTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
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
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => codecept_data_dir() . 'project.php',
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_project_rel.php',
            ],
        ]);
    }

    /**
     * `UserProjectRel::getUser()` relation query method test.
     */
    public function testGetUser()
    {
        $model = UserProjectRel::findOne(1001);
        $query = $model->getUser();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid User model', $model->user)->isInstanceOf(User::className());
        verify('Query result user id should match', $model->user->id)->equals($model->userId);
    }

    /**
     * `UserProjectRel::getProject()` relation query method test.
     */
    public function testGetProject()
    {
        $model = UserProjectRel::findOne(1001);
        $query = $model->getProject();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid Project model', $model->project)->isInstanceOf(Project::className());
        verify('Query result project id should match', $model->project->id)->equals($model->projectId);
    }
}
