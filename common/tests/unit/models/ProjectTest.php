<?php
namespace common\tests\unit\models;

use Yii;
use yii\db\ActiveQuery;
use common\models\User;
use common\models\Project;
use common\models\Version;
use common\models\Screen;
use common\models\ScreenComment;
use common\models\ProjectPreview;
use common\models\UserProjectRel;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\UserSettingFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ScreenCommentFixture;
use common\tests\fixtures\ProjectPreviewFixture;
use common\tests\fixtures\UserProjectRelFixture;

/**
 * Project AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectTest extends \Codeception\Test\Unit
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
            'userSetting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_setting.php',
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => codecept_data_dir() . 'project.php',
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => codecept_data_dir() . 'version.php',
            ],
            'screen' => [
                'class'    => ScreenFixture::className(),
                'dataFile' => codecept_data_dir() . 'screen.php',
            ],
            'comment' => [
                'class'    => ScreenCommentFixture::className(),
                'dataFile' => codecept_data_dir() . 'screen_comment.php',
            ],
            'preview' => [
                'class'    => ProjectPreviewFixture::className(),
                'dataFile' => codecept_data_dir() . 'project_preview.php',
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_project_rel.php',
            ],
        ]);
    }

    /**
     * `Project::setPassword()` method test.
     */
    public function testSetPassword()
    {
        $project = new Project;
        verify('Password hash not to be set', $project->passwordHash)->isEmpty();
        $project->setPassword(123456);
        verify('Password hash to be set', $project->passwordHash)->notEmpty();
    }

    /**
     * `Project::validatePassword()` method test.
     */
    public function testValidatePassword()
    {
        $project = Project::findOne(1002);

        $this->specify('INVALID project password', function () use ($project) {
            verify($project->validatePassword('invalid-password'))->false();
        });

        $this->specify('VALID project password', function () use ($project) {
            verify($project->validatePassword('123456'))->true();
        });
    }

    /**
     * `Project::isPasswordProtected()` method test.
     */
    public function testIsPasswordProtected()
    {
        $this->specify('Project WITHOUT password', function() {
            $project = Project::findOne(1001);
            verify($project->isPasswordProtected())->false();
        });

        $this->specify('Project WITH password', function() {
            $project = Project::findOne(1002);
            verify($project->isPasswordProtected())->true();
        });
    }

    /**
     * `Project::getUploadDir()` method test.
     */
    public function testGetUploadDir()
    {
        $project = Project::findOne(1001);
        $str     = $project->getUploadDir();

        verify('Not to be empty', $str)->notEmpty();
        verify('Should begins with the public upload path', $str)->startsWith(Yii::getAlias('@mainWeb'));
        verify('Should contains a project identifier', $str)->contains('/' . md5($project->id));
    }

    /**
     * `Project::createDefaultVersion()` method test.
     */
    public function testCreateDefaultVersion()
    {
        $project       = Project::findOne(1002);
        $initialCount  = $project->getVersions()->count();
        $result        = $project->createDefaultVersion();
        $latestVersion = $project->getVersions()->orderBy(['createdAt' => SORT_DESC])->one();

        verify('Method should succeed', $result)->true();
        verify('New version should be created', $project->getVersions()->count())->equals($initialCount + 1);
        verify('Version project id should match', $latestVersion->projectId)->equals($project->id);
        verify('Version title should match', $latestVersion->title)->equals(null);
        verify('Version type should match', $latestVersion->type)->equals(Version::TYPE_DESKTOP);
        verify('Version subtype should match', $latestVersion->subtype)->equals(null);
        verify('Version scaleFactor should match', $latestVersion->scaleFactor)->equals(Version::DEFAULT_SCALE_FACTOR);
    }

    /**
     * `Project::createDefaultPreviews()` method test.
     */
    public function testCreateDefaultPreviews()
    {
        $this->specify('Project WITHOUT missing preview type', function() {
            $project      = Project::findOne(1001);
            $beforeCreate = count($project->getPreviews()->all());

            $project->createDefaultPreviews();

            verify(count($project->getPreviews()->all()))->equals($beforeCreate);
        });

        $this->specify('Project WITH missing preview type', function() {
            $project = Project::findOne(1002);

            // before create
            verify(count($project->getPreviews()->all()))->equals(1);

            $project->createDefaultPreviews();

            // after create
            verify(count($project->getPreviews()->all()))->equals(2);
        });
    }

    /**
     * `Project::getPreviewUrl()` method test.
     */
    public function testGetPreviewUrl()
    {
        $this->specify('Project with missing preview type', function() {
            $project = Project::findOne(1002);
            $url     = $project->getPreviewUrl(ProjectPreview::TYPE_VIEW_AND_COMMENT);

            verify('Generated url should match', $url)->equals('#');
        });

        $this->specify('Project with existing preview type', function() {
            $project = Project::findOne(1002);
            $url     = $project->getPreviewUrl(ProjectPreview::TYPE_VIEW);

            verify('Url should contains the preview slug', $url)->contains('BAgePG5c');
        });

        $this->specify('Project with existing preview type and additional query params', function() {
            $project = Project::findOne(1002);
            $url = $project->getPreviewUrl(ProjectPreview::TYPE_VIEW, ['_custom_param' => 'lorem']);

            verify('Url should contains the preview slug', $url)->contains('BAgePG5c');
            verify('Url should contains the additional param', $url)->contains('_custom_param=lorem');
        });
    }

    /**
     * `Project::linkUser()` method test.
     */
    public function testLinkUser()
    {
        $this->specify('Link existing user', function() {
            $user    = User::findOne(1002);
            $project = Project::findOne(1001);
            $oldUserProjectRelsCount = count(UserProjectRel::findAll(['userId' => $user->id, 'projectId' => $project->id]));

            verify('Should complete successfuly', $project->linkUser($user))->true();
            verify('UserProjectRels count should be the same', UserProjectRel::findAll(['userId' => $user->id, 'projectId' => $project->id]))->count($oldUserProjectRelsCount);
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Link new user without notification email', function() {
            $user    = User::findOne(1003);
            $project = Project::findOne(1001);

            verify('Should complete successfuly', $project->linkUser($user, false))->true();
            verify('User rel should exist', UserProjectRel::findOne(['userId' => $user->id, 'projectId' => $project->id]))->isInstanceOf(UserProjectRel::className());
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Link new user and send notification email', function() {
            $user    = User::findOne(1004);
            $project = Project::findOne(1001);

            verify('Should complete successfuly', $project->linkUser($user))->true();
            verify('User rel should exist', UserProjectRel::findOne(['userId' => $user->id, 'projectId' => $project->id]))->isInstanceOf(UserProjectRel::className());
            $this->tester->seeEmailIsSent();
        });
    }

    /**
     * `Project::unlinkUser()` method test.
     */
    public function testUnlinkUser()
    {
        $this->specify('Try to unlink user from a project with only 1 administrator', function() {
            $user    = User::findOne(1002);
            $project = Project::findOne(1001);

            verify('Should return false', $project->unlinkUser($user))->false();
            verify('User rel should exist', UserProjectRel::findOne(['userId' => $user->id, 'projectId' => $project->id]))->notEmpty();
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Unlink user without email notification from a project with more than 2 administrators', function() {
            $user    = User::findOne(1003);
            $project = Project::findOne(1002);

            verify('Should complete successfuly', $project->unlinkUser($user, false))->true();
            verify('User rel should not exist', UserProjectRel::findOne(['userId' => $user->id, 'projectId' => $project->id]))->null();
            $this->tester->dontSeeEmailIsSent();
        });

        $this->specify('Unlink user with email notification from a project with more than 2 administrators', function() {
            $user    = User::findOne(1003);
            $project = Project::findOne(1004);

            verify('Should complete successfuly', $project->unlinkUser($user))->true();
            verify('User rel should not exist', UserProjectRel::findOne(['userId' => $user->id, 'projectId' => $project->id]))->null();
            $this->tester->seeEmailIsSent();
        });
    }

    /**
     * `Project::sendAddedProjectAdminEmail()` method test.
     */
    public function testSendAddedProjectAdminEmail()
    {
        $user   = User::findOne(1005);
        $model  = Project::findOne(1001);
        $result = $model->sendAddedProjectAdminEmail($user);

        $this->tester->seeEmailIsSent();
        $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
        verify('Mail method should succeed', $result)->true();
        verify('Receiver email should match', $message->getTo())->hasKey($user->email);
        verify('Body should contains a project url', current($message->getChildren())->getBody())->contains(
            Yii::$app->mainUrlManager->createUrl(['projects/view', 'id' => $model->id], true)
        );
    }

    /**
     * `Project::sendRemovedProjectAdminEmail()` method test.
     */
    public function testSendRemovedProjectAdminEmail()
    {
        $user   = User::findOne(1005);
        $model  = Project::findOne(1001);
        $result = $model->sendRemovedProjectAdminEmail($user);

        $this->tester->seeEmailIsSent();
        $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
        verify('Mail method should succeed', $result)->true();
        verify('Receiver email should match', $message->getTo())->hasKey($user->email);
    }

    /* ===============================================================
     * Relations
     * ============================================================ */
    /**
     * `Project::getUserRels()` relation query method test.
     */
    public function testGetUserRels()
    {
        $model = Project::findOne(1001);
        $query = $model->getUserRels();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasMany relation', $query->multiple)->true();
        verify('Query result should not be empty', $model->userRels)->notEmpty();
        foreach ($model->userRels as $rel) {
            verify('Query result item should be valid UserProjectRel model', $rel)->isInstanceOf(UserProjectRel::className());
            verify('Query result item projectId should match', $rel->projectId)->equals($model->id);
        }
    }

    /**
     * `Project::getUsers()` relation query method test.
     */
    public function testGetUsers()
    {
        $model        = Project::findOne(1002);
        $query        = $model->getUsers();
        $validUserIds = [1003, 1004];

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasMany relation', $query->multiple)->true();
        verify('Query result should not be empty', $model->users)->notEmpty();
        foreach ($model->users as $user) {
            verify('Query result item should be valid User model', $user)->isInstanceOf(User::className());
            verify('Query result item user id should exist', in_array($user->id, $validUserIds))->true();
        }
    }

    /**
     * `Project::getVersions()` relation query method test.
     */
    public function testGetVersions()
    {
        $model = Project::findOne(1001);
        $query = $model->getVersions();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasMany relation', $query->multiple)->true();
        verify('Query result should not be empty', $model->versions)->notEmpty();
        foreach ($model->versions as $version) {
            verify('Query result item should be valid Version model', $version)->isInstanceOf(Version::className());
            verify('Query result item projectId should match', $version->projectId)->equals($model->id);
        }
    }

    /**
     * `Project::getPreviews()` relation query method test.
     */
    public function testGetPreviews()
    {
        $model = Project::findOne(1001);
        $query = $model->getPreviews();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasMany relation', $query->multiple)->true();
        verify('Query result should not be empty', $model->previews)->notEmpty();
        foreach ($model->previews as $preview) {
            verify('Query result item should be valid ProjectPreview model', $preview)->isInstanceOf(ProjectPreview::className());
            verify('Query result item projectId should match', $preview->projectId)->equals($model->id);
        }
    }

    /**
     * `Project::getScreens()` relation query method test.
     */
    public function testGetScreens()
    {
        $this->specify('Project WITHOUT related Screen models', function() {
            $model = Project::findOne(1004);
            $query = $model->getScreens();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be an empty array', $model->screens)->count(0);
        });

        $this->specify('Project WITH related Screen models', function() {
            $model          = Project::findOne(1001);
            $query          = $model->getScreens();
            $validScreenIds = [1001, 1002];

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty', $model->screens)->notEmpty();
            foreach ($model->screens as $screen) {
                verify('Query result item should be valid Screen model', $screen)->isInstanceOf(Screen::className());
                verify('Query result item screen id should exist', in_array($screen->id, $validScreenIds))->true();
            }
        });
    }

    /**
     * `Project::getFeaturedScreen()` relation query method test.
     */
    public function testGetFeaturedScreen()
    {
        $this->specify('Project WITHOUT related Screen model', function() {
            $model = Project::findOne(1004);
            $query = $model->getFeaturedScreen();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be null', $model->featuredScreen)->null();
        });

        $this->specify('Project WITH related Screen model', function() {
            $model = Project::findOne(1001);
            $query = $model->getFeaturedScreen();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result item should be valid Screen model', $model->featuredScreen)->isInstanceOf(Screen::className());
            verify('Query result item screen id should match', $model->featuredScreen->id)->equals(1001);
        });
    }

    /**
     * `Project::getLatestActiveVersion()` relation query method test.
     */
    public function testGetLatestActiveVersion()
    {
        $this->specify('Project WITHOUT related active Version model', function() {
            $model = Project::findOne(1004);
            $query = $model->getLatestActiveVersion();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be null', $model->latestActiveVersion)->null();
        });

        $this->specify('Project WITH related active Version model', function() {
            $model = Project::findOne(1001);
            $query = $model->getFeaturedScreen();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result item should be valid Screen model', $model->latestActiveVersion)->isInstanceOf(Version::className());
            verify('Query result item screen id should match', $model->latestActiveVersion->id)->equals(1001);
        });
    }


    /* ===============================================================
     * Query trait methods
     * ============================================================ */
    /**
     * Tests whether `Project::findVersionById()` returns Version model
     * belonging to a specific project.
     */
    public function testFindVersionById()
    {
        $project = Project::findOne(1001);

        $this->specify('Nonexisting version', function () use ($project) {
            $version = $project->findVersionById(0);
            verify($version)->null();
        });

        $this->specify('Existing version from a different project', function () use ($project) {
            $version = $project->findVersionById(1003);
            verify($version)->null();
        });

        $this->specify('Existing version from the current project', function () use ($project) {
            $version = $project->findVersionById(1001);
            verify($version)->isInstanceOf(Version::className());
        });
    }

    /**
     * Tests whether `Project::findScreensQuery()` generates valid Screen models query.
     */
    public function testFindScreensQuery()
    {
        $project = Project::findOne(1001);

        $this->specify('Valid ActiveQuery object', function () use ($project) {
            $query = $project->findScreensQuery([1001, 1002]);
            verify($query)->isInstanceOf(ActiveQuery::className());
        });

        $this->specify('Nonexisting screen(s)', function () use ($project) {
            $screens = $project->findScreensQuery(12345)->all();
            verify($screens)->isEmpty();
        });

        $this->specify('Existing screens from a different project', function () use ($project) {
            $screens = $project->findScreensQuery([1003])->all();
            verify($screens)->isEmpty();
        });

        $this->specify('Existing screens from the current project', function () use ($project) {
            $screens = $project->findScreensQuery([1001, 1002])->all();
            verify(count($screens))->equals(2);
        });
    }

    /**
     * Tests whether `Project::findScreenById()` returns Screen model
     * belonging to a specific project.
     */
    public function testFindScreenById()
    {
        $project = Project::findOne(1001);

        $this->specify('Nonexisting screen', function () use ($project) {
            $screen = $project->findScreenById(0);
            verify($screen)->null();
        });

        $this->specify('Existing screen from a different project', function () use ($project) {
            $screen = $project->findScreenById(1003);
            verify($screen)->null();
        });

        $this->specify('Existing screen from the current project', function () use ($project) {
            $screen = $project->findScreenById(1001);
            verify($screen)->isInstanceOf(Screen::className());
        });
    }

    /**
     * Tests whether `Project::findScreenCommentById()` returns ScreenComment model
     * belonging to a screen from a specific project.
     */
    public function testFindScreenCommentById()
    {
        $project = Project::findOne(1001);

        $this->specify('Nonexisting comment', function () use ($project) {
            $comment = $project->findScreenCommentById(0);
            verify($comment)->null();
        });

        $this->specify('Existing comment from a screen owned by a different user', function () use ($project) {
            $comment = $project->findScreenCommentById(1004);
            verify($comment)->null();
        });

        $this->specify('Existing comment from a screen owned by the current user', function () use ($project) {
            $comment = $project->findScreenCommentById(1001);
            verify($comment)->isInstanceOf(ScreenComment::className());
        });
    }

    /**
     * Tests whether `Project::findPreviewByType()` returns
     * specific ProjectPreview model of a project based on its type.
     */
    public function testFindPreviewByType()
    {
        $project = Project::findOne(1001);

        $this->specify('Nonexisting ProjectPreview model', function () use ($project) {
            $preview = $project->findPreviewByType('invalid_type');
            verify($preview)->null();
        });

        $this->specify('Existing ProjectPreview model', function () use ($project) {
            $preview = $project->findPreviewByType(ProjectPreview::TYPE_VIEW_AND_COMMENT);
            verify('The model must be an instance of ProjectPreview', $preview)->isInstanceOf(ProjectPreview::className());
            verify('The model must has the correct type', $preview->type)->equals(ProjectPreview::TYPE_VIEW_AND_COMMENT);
        });
    }

    /**
     * `Project::findAllCommenters()` method test.
     */
    public function testFindAllCommenters()
    {
        $project = Project::findOne(1003);

        $this->specify('With mention user setting check', function () use ($project) {
            $result = $project->findAllCommenters();

            verify('The result should be an array', is_array($result))->true();
            verify('Result count should match', $result)->count(2);
            verify('Guest email should exist', $result)->hasKey('loremipsum@presentator.io');
            verify('Registered User email should exist', $result)->hasKey('test3@presentator.io');
            $this->tester->checkMentionResultItems($result);
        });

        $this->specify('Without mention user setting check', function () use ($project) {
            $result = $project->findAllCommenters(false);

            verify('The result should be an array', is_array($result))->true();
            verify('Result count should match', $result)->count(3);
            verify('Guest email should exist', $result)->hasKey('loremipsum@presentator.io');
            verify('Registered User email should exist', $result)->hasKey('test3@presentator.io');
            verify('Registered User email should exist', $result)->hasKey('test5@presentator.io');
            $this->tester->checkMentionResultItems($result);
        });

        $this->specify('Try to find all commenters for a project without any comments', function () {
            $project = Project::findOne(1004);
            $result = $project->findAllCommenters();

            verify('The result should be an array', is_array($result))->true();
            verify('Should not found any mention users', $result)->count(0);
        });
    }
}
