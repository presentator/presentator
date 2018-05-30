<?php
namespace app\tests\models;

use Yii;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\ProjectPreviewFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\models\Project;
use common\models\ProjectPreview;
use app\models\ProjectShareForm;

/**
 * ProjectShareForm model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectShareFormTest extends \Codeception\Test\Unit
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
     * `ProjectShareForm:getProject()` method test.
     */
    public function testGetProject()
    {
        $project = Project::findOne(1001);
        $model   = new ProjectShareForm($project);

        verify('Should return Project instance', $model->getProject())->isInstanceOf(Project::className());
    }

    /**
     * `ProjectShareForm:validateEmails()` method test.
     */
    public function testValidateEmails()
    {
        $project = Project::findOne(1001);

        $this->specify('INVALID email(s)', function() use ($project) {
            $model = new ProjectShareForm($project, [
                'email' => 'invalid_email.com, valid_email@presentator.io',
            ]);
            $model->validateEmails('email', []);

            verify('Error message should be set', $model->errors)->hasKey('email');
        });

        $this->specify('VALID email(s)', function() use ($project) {
            $model = new ProjectShareForm($project, [
                'email' => 'valid_email1@presentator.io, valid_email2@presentator.io',
            ]);
            $model->validateEmails('email', []);

            verify('Error message should not be set', $model->errors)->hasntKey('email');
        });
    }

    /**
     * `ProjectShareForm:send()` method test.
     */
    public function testSend()
    {
        $project = Project::findOne(1001);

        $this->specify('False send attempt', function() use ($project) {
            $model = new ProjectShareForm($project, [
                'email'   => 'invalid_email',
                'message' => 'My test optional message...',
            ]);

            verify('Model should not validate', $model->send())->false();
            verify('Email error message should be set', $model->errors)->hasKey('email');
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Correct send attempt', function() use ($project) {
            $model = new ProjectShareForm($project, [
                'email'         => 'valid_email@presentator.io',
                'message'       => 'My test optional message...',
                'allowComments' => true,
            ]);
            $preview = $project->findPreviewByType(ProjectPreview::TYPE_VIEW_AND_COMMENT);
            $result  = $model->send();

            verify('Model should validate successfully', $result)->true();
            verify('Email error message should not be set', $model->errors)->hasntKey('email');

            // email check
            $this->tester->seeEmailIsSent();
            $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
            $body = current($message->getChildren())->getBody();

            verify('To email should match', $message->getTo())->hasKey('valid_email@presentator.io');
            verify('Body should contains a project preview url.', $body)->contains(
                Yii::$app->mainUrlManager->createUrl(['preview/view', 'slug' => $preview->slug], true)
            );
            verify('Body should contains the user message', $body)->contains('My test optional message...');
        });
    }
}
