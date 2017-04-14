<?php
namespace common\tests\unit\models;

use Yii;
use yii\db\ActiveQuery;
use yii\base\NotSupportedException;
use common\models\User;
use common\models\Project;
use common\models\Version;
use common\models\Screen;
use common\models\ScreenComment;
use common\models\UserSetting;
use common\models\UserAuth;
use common\models\UserProjectRel;
use common\models\UserScreenCommentRel;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;
use common\tests\fixtures\ScreenCommentFixture;
use common\tests\fixtures\UserSettingFixture;
use common\tests\fixtures\UserAuthFixture;
use common\tests\fixtures\UserProjectRelFixture;
use common\tests\fixtures\UserScreenCommentRelFixture;

/**
 * @todo Check emails after registration, password reset requests, etc.

 * User AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class UserTest extends \Codeception\Test\Unit
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
            'userScreenCommentRel' => [
                'class'    => UserScreenCommentRelFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_screen_comment_rel.php',
            ],
            'userProjectRel' => [
                'class'    => UserProjectRelFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_project_rel.php',
            ],
            'setting' => [
                'class'    => UserSettingFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_setting.php',
            ],
            'auth' => [
                'class'    => UserAuthFixture::className(),
                'dataFile' => codecept_data_dir() . 'user_auth.php',
            ],
        ]);
    }

    /**
     * `User::getFullName()` method test.
     */
    public function testGetFullName()
    {
        $this->specify('User WITH first and last name', function() {
            $user = User::findOne(1001);
            verify('User first and last name', $user->getFullName())->equals('Gani Georgiev');
        });

        $this->specify('User WITH only first name', function() {
            $user = User::findOne(1002);
            verify('User first name', $user->getFullName())->equals('Ivan');
        });

        $this->specify('User WITH only last name', function() {
            $user = User::findOne(1004);
            verify('User last name', $user->getFullName())->equals('Petrov');
        });

        $this->specify('User WITHOUT first and last name', function() {
            $user = User::findOne(1003);
            verify('Empty string', $user->getFullName())->equals('');
        });
    }

    /**
     * `User::getIdentificator()` method test.
     */
    public function testGetIdentificator()
    {
        $this->specify('User WITH first and last name', function() {
            $user = User::findOne(1001);
            verify('User first name', $user->getIdentificator('firstName'))->equals('Gani');
            verify('User last name', $user->getIdentificator('lastName'))->equals('Georgiev');
            verify('User email', $user->getIdentificator('email'))->equals('test1@presentator.io');
            verify('User first and last name', $user->getIdentificator(null))->equals('Gani Georgiev');
        });

        $this->specify('User WITHOUT first and last name', function() {
            $user = User::findOne(1003);
            verify('User email as fallback for missing first name', $user->getIdentificator('firstName'))->equals('test3@presentator.io');
            verify('User email as fallback for missing last name', $user->getIdentificator('lastName'))->equals('test3@presentator.io');
            verify('User email as fallback for missing first and last name', $user->getIdentificator(null))->equals('test3@presentator.io');
        });
    }

    /**
     * `User::sendActivationEmail()` method test.
     */
    public function testSendActivationEmail()
    {
        $user   = User::findOne(1001);
        $result = $user->sendActivationEmail();

        $this->tester->seeEmailIsSent();
        $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
        verify('Mail method should succeed', $result)->true();
        verify('Receiver email should match', $message->getTo())->hasKey($user->email);
        verify('Body should contains an activation url', current($message->getChildren())->getBody())->contains(
            Yii::$app->mainUrlManager->createUrl(['site/activation', 'email' => $user->email, 'token' => $user->getActivationToken()], true)
        );
    }

    /**
     * `User::sendPasswordResetEmail()` method test.
     */
    public function testSendPasswordResetEmail()
    {
        $user   = User::findOne(1003);
        $result = $user->sendPasswordResetEmail();

        $this->tester->seeEmailIsSent();
        $message = $this->tester->grabLastSentEmail()->getSwiftMessage();
        verify('Mail method should succeed', $result)->true();
        verify('Receiver email should match', $message->getTo())->hasKey($user->email);
        verify('Body should contains an activation url', current($message->getChildren())->getBody())->contains(
            Yii::$app->mainUrlManager->createUrl(['site/reset-password', 'token' => $user->passwordResetToken], true)
        );
    }

    /* ===============================================================
     * Relations
     * ============================================================ */
    /**
     * `User::getUserAuths()` relation query method test.
     */
    public function testGetUserAuths()
    {
        $this->specify('User WITHOUT related UserAuth models', function() {
            $user  = User::findOne(1003);
            $query = $user->getUserAuths();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->userAuths)->count(0);
        });

        $this->specify('User WITH related UserAuth models', function() {
            $user  = User::findOne(1002);
            $query = $user->getUserAuths();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->userAuths)->count(2);
            foreach ($user->userAuths as $model) {
                verify($model)->isInstanceOf(UserAuth::className());
            }
        });
    }

    /**
     * `User::getUserSettings()` relation query method test.
     */
    public function testGetUserSettings()
    {
        $this->specify('User WITHOUT related UserSetting models', function() {
            $user  = User::findOne(1005);
            $query = $user->getUserSettings();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->userSettings)->count(0);
        });

        $this->specify('User WITH related UserSetting models', function() {
            $user  = User::findOne(1002);
            $query = $user->getUserSettings();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->userSettings)->count(1);
            foreach ($user->userSettings as $model) {
                verify($model)->isInstanceOf(UserSetting::className());
            }
        });
    }

    /**
     * `User::getProjectRels()` relation query method test.
     */
    public function testGetProjectRels()
    {
        $this->specify('User WITHOUT related UserProjectRel models', function() {
            $user  = User::findOne(1005);
            $query = $user->getProjectRels();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->projectRels)->count(0);
        });

        $this->specify('User WITH related UserProjectRel models', function() {
            $user  = User::findOne(1003);
            $query = $user->getProjectRels();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->projectRels)->count(3);
            foreach ($user->projectRels as $model) {
                verify($model)->isInstanceOf(UserProjectRel::className());
                verify('User id should match', $model->userId)->equals($user->id);
            }
        });
    }

    /**
     * `User::getProjects()` relation query method test.
     */
    public function testGetProjects()
    {
        $this->specify('User WITHOUT related Project models', function() {
            $user  = User::findOne(1005);
            $query = $user->getProjects();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->projects)->count(0);
        });

        $this->specify('User WITH related Project models', function() {
            $user  = User::findOne(1003);
            $query = $user->getProjects();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->projects)->count(3);

            $validIds = [1002, 1003, 1004];
            foreach ($user->projects as $model) {
                verify($model)->isInstanceOf(Project::className());
                verify('Should be a project owned by the user', in_array($model->id, $validIds))->true();
            }
        });
    }

    /**
     * `User::getVersions()` relation query method test.
     */
    public function testGetVersions()
    {
        $this->specify('User WITHOUT related Version models', function() {
            $user  = User::findOne(1005);
            $query = $user->getVersions();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->versions)->count(0);
        });

        $this->specify('User WITH related Version models', function() {
            $user  = User::findOne(1003);
            $query = $user->getVersions();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->versions)->count(4);

            $validIds = [1003, 1004, 1005, 1006];
            foreach ($user->versions as $model) {
                verify($model)->isInstanceOf(Version::className());
                verify('Should be a version owned by the user', in_array($model->id, $validIds))->true();
            }
        });
    }

    /**
     * `User::getScreens()` relation query method test.
     */
    public function testGetScreens()
    {
        $this->specify('User WITHOUT related Screen models', function() {
            $user  = User::findOne(1005);
            $query = $user->getScreens();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->screens)->count(0);
        });

        $this->specify('User WITH related Screen models', function() {
            $user  = User::findOne(1003);
            $query = $user->getScreens();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->screens)->count(2);

            $validIds = [1003, 1004];
            foreach ($user->screens as $model) {
                verify($model)->isInstanceOf(Screen::className());
                verify('Should be a screen owned by the user', in_array($model->id, $validIds))->true();
            }
        });
    }

    /**
     * `User::getScreenComments()` relation query method test.
     */
    public function testGetScreenComments()
    {
        $this->specify('User WITHOUT related ScreenComment models', function() {
            $user  = User::findOne(1005);
            $query = $user->getScreenComments();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->screenComments)->count(0);
        });

        $this->specify('User WITH related ScreenComment models', function() {
            $user  = User::findOne(1003);
            $query = $user->getScreenComments();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->screenComments)->count(3);

            $validIds = [1004, 1005, 1006];
            foreach ($user->screenComments as $model) {
                verify($model)->isInstanceOf(ScreenComment::className());
                verify('Should be a comment owned by the user', in_array($model->id, $validIds))->true();
            }
        });
    }

    /**
     * `User::getScreenCommentRels()` relation query method test.
     */
    public function testGetScreenCommentRels()
    {
        $this->specify('User WITHOUT related UserScreenCommentRel models', function() {
            $user  = User::findOne(1005);
            $query = $user->getScreenCommentRels();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be empty array', $user->screenCommentRels)->count(0);
        });

        $this->specify('User WITH related UserScreenCommentRel models', function() {
            $user  = User::findOne(1003);
            $query = $user->getScreenCommentRels();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty array', $user->screenCommentRels)->count(2);
            foreach ($user->screenCommentRels as $model) {
                verify($model)->isInstanceOf(UserScreenCommentRel::className());
                verify('User id should match', $model->userId)->equals($user->id);
            }
        });
    }

    /* ===============================================================
     * Identity interface methods
     * ============================================================ */
    /**
     * Tests whether `User::findIdentity()` returns active User model by id.
     */
    public function testFindIdentity()
    {
        $this->specify('Non existing user', function() {
            $user = User::findIdentity(0);
            verify($user)->null();
        });

        $this->specify('Existing INACTIVE user', function() {
            $user = User::findIdentity(1001);
            verify($user)->null();
        });

        $this->specify('Existing active user', function() {
            $user = User::findIdentity(1002);
            verify($user)->isInstanceOf(User::className());
        });
    }

    /**
     * `User::findIdentityByAccessToken()` method test.
     */
    public function testFindIdentityByAccessToken()
    {
        $this->specify('Method is not supported', function() {
            User::findIdentityByAccessToken('blah-blah-blah');
        }, ['throws' => new NotSupportedException]);
    }

    /**
     * `User::getId()` method test.
     */
    public function testGetId()
    {
        $user = User::findOne(1002);

        verify('Should return the user id', $user->getId())->equals($user->id);
    }

    /**
     * `User::getAuthKey()` method test.
     */
    public function testGetAuthKey()
    {
        $user = User::findOne(1002);

        verify('Should return the user authKey', $user->getAuthKey())->equals($user->authKey);
    }

    /**
     * Tests whether `User::validateAuthKey()` validates correctly an user auth key.
     */
    public function testValidateAuthKey()
    {
        $user = User::findOne(1002);

        $this->specify('INVALID auth key', function() use ($user) {
            verify($user->validateAuthKey('blah-blah-blah'))->false();
        });

        $this->specify('VALID auth key', function() use ($user) {
            verify($user->validateAuthKey($user->authKey))->true();
        });
    }

    /**
     * Tests whether `User::generateAuthKey()` generate and set a properly formatted auth key.
     */
    public function testGenerateAuthKey()
    {
        $user = new User;
        $user->generateAuthKey();

        verify('Checks if the `authKey` property is set', $user->authKey)->notEmpty();
    }

    /* ===============================================================
     * Tests for helper methods related to passwords, tokens, etc.
     * ============================================================ */
    /**
     * `User::setPassword()` method test.
     */
    public function testSetPassword()
    {
        $user = new User;
        verify('Password hash not to be set', $user->passwordHash)->isEmpty();

        $user->setPassword(123456);
        verify('Password hash to be set', $user->passwordHash)->notEmpty();
    }

    /**
     * `User::validatePassword()` method test.
     */
    public function testValidatePassword()
    {
        $user = User::findOne(1003);

        $this->specify('INVALID user password', function() use ($user) {
            verify($user->validatePassword('invalid-password'))->false();
        });

        $this->specify('VALID user password', function() use ($user) {
            verify($user->validatePassword('123456'))->true();
        });
    }

    /**
     * `User::isPasswordResetTokenValid()` method test.
     */
    public function testIsPasswordResetTokenValid()
    {
        $this->specify('INVALID/EXPIRED password reset token', function() {
            $invalidToken = Yii::$app->security->generateRandomString() . '_' . strtotime('-3 days');
            verify(User::isPasswordResetTokenValid($invalidToken))->false();
        });

        $this->specify('VALID password reset token', function() {
            $validToken = Yii::$app->security->generateRandomString() . '_' . time();
            verify(User::isPasswordResetTokenValid($validToken))->true();
        });
    }

    /**
     * `User::generatePasswordResetToken()` method test.
     */
    public function testGeneratePasswordResetToken()
    {
        $user = new User;
        $user->generatePasswordResetToken();
        $token = $user->passwordResetToken;

        $this->specify('Password reset token must be set', function() use ($token) {
            verify($token)->notEmpty();
        });

        $this->specify('Password reset token must be valid', function() use ($token) {
            verify(User::isPasswordResetTokenValid($token))->true();
        });
    }

    /**
     * `User::removePasswordResetToken()` method test.
     */
    public function testRemovePasswordResetToken()
    {
        $user = User::findOne(1003);
        verify('Have password reset token', $user->passwordResetToken)->notEmpty();

        $user->removePasswordResetToken();
        verify('Password reset token is unset', $user->passwordResetToken)->isEmpty();
    }

    /**
     * `User::generateJwtToken()` method test.
     */
    public function testGenerateJwtToken()
    {
        $user  = User::findOne(1002);
        $token = $user->generateJwtToken();
        $parts = explode('.', $token);

        $this->specify('Generates valid JWT token string', function() use ($user, $token, $parts) {
            verify($token)->notEmpty();
            verify($parts)->count(3);
        });

        $this->specify('Checks payload token data', function() use ($user, $token, $parts) {
            $payload = json_decode(base64_decode($parts[1]));
            verify($payload)->notEmpty();
            verify($payload->userId)->equals($user->id);
            verify($payload->userEmail)->equals($user->email);
        });
    }

    /**
     * `User::getActivationToken()` method test.
     */
    public function testGetActivationToken()
    {
        $user  = User::findOne(1002);
        $token = $user->getActivationToken();

        verify('Should be none empty string', $token)->notEmpty();
    }

    /**
     * `User::validateActivationToken()` method test.
     */
    public function testValidateActivationToken()
    {
        $user  = User::findOne(1002);

        $this->specify('INVALID validation token', function() use ($user) {
            $invalidToken = '8d42ae83f6ca9a59de725475df3caa07';

            verify('Should be invalid', $user->validateActivationToken($invalidToken))->false();
        });

        $this->specify('VALID validation token', function() use ($user) {
            $validToken = $user->getActivationToken();

            verify('Should be valid', $user->validateActivationToken($validToken))->true();
        });
    }

    /* ===============================================================
     * Queries
     * ============================================================ */
    /**
     * Tests whether `User::findByEmail()` returns active User model by its email address.
     */
    public function testFindByEmail()
    {
        $this->specify('Existing INACTIVE user', function() {
            $user = User::findByEmail('test1@presentator.io');
            verify($user)->null();
        });

        $this->specify('Existing ACTIVE user', function() {
            $user = User::findByEmail('test2@presentator.io');
            verify($user)->isInstanceOf(User::className());
        });

        $this->specify('Non existing user', function() {
            $user = User::findByEmail('none_existing_email@presentator.io');
            verify($user)->null();
        });
    }

    /**
     * Tests whether `User::findByPasswordResetToken()` returns
     * active User model by valid password reset token string.
     */
    public function testFindByPasswordResetToken()
    {
        $this->specify('Non existing password reset token', function() {
            $user = User::findByPasswordResetToken('blah-blah-blah');
            verify($user)->null();
        });

        $this->specify('Inactive user with existing VALID password reset token', function() {
            $inactiveUser = User::findOne(1001);
            $user         = User::findByPasswordResetToken($inactiveUser->passwordResetToken);

            verify($user)->null();
        });

        $this->specify('Active user with existing INVALID password reset token', function() {
            $invalidUser = User::findOne(1003);
            $user        = User::findByPasswordResetToken($invalidUser->passwordResetToken);

            verify($user)->null();
        });

        $this->specify('Active user with existing VALID password reset token', function() {
            $validUser = User::findOne(1004);
            $user      = User::findByPasswordResetToken($validUser->passwordResetToken);

            verify($user)->isInstanceOf(User::className());
        });
    }

    /**
     * Tests whether `User::findByJwtToken()` returns
     * active User model by valid JWT token string.
     */
    public function testFindByJwtToken()
    {
        $this->specify('INVALID JWT token', function() {
            $user = User::findByJwtToken('invalid.existing.token');
            verify($user)->null();
        });

        $this->specify('VALID token for INACTIVE user', function() {
            $inactiveUser = User::findOne(1001);
            $user         = User::findByJwtToken($inactiveUser->generateJwtToken());
            verify($user)->null();
        });

        $this->specify('VALID token ACTIVE user', function() {
            $activeUser = User::findOne(1002);
            $user       = User::findByJwtToken($activeUser->generateJwtToken());
            verify($user)->isInstanceOf(User::className());
        });
    }

    /**
     * `User::searchUsers()` method test.
     */
    public function testSearchUsers()
    {
        $this->specify('Search for INACTIVE users by name part', function() {
            $users = User::searchUsers('Gani Georgiev');
            verify('Should be empty', $users)->isEmpty();
        });

        $this->specify('Search for INACTIVE users by email part', function() {
            $users = User::searchUsers('test1@presentator.io');
            verify('Should be empty', $users)->isEmpty();
        });

        $this->specify('Search for ACTIVE users by name part', function() {
            $search = 'Lorem';
            $users  = User::searchUsers($search);

            verify('Should found 1 active user', $users)->count(1);
            foreach ($users as $user) {
                verify('Should contains the search keyword', strtoupper($user->getFullName()))->contains(strtoupper($search));
            }
        });

        $this->specify('Search for ACTIVE users by email part', function() {
            $search = '@presentator.io';
            $users  = User::searchUsers($search);

            verify('Should found 4 active user', $users)->count(5);
            foreach ($users as $user) {
                verify('Should contains the search keyword', $user->email)->contains($search);
            }
        });

        $this->specify('Search for ACTIVE users by email part with exclude', function() {
            $search = '@presentator.io';
            $users  = User::searchUsers($search, [1004, 1005]);

            verify('Users count should match', $users)->count(3);
            foreach ($users as $user) {
                verify('Should contains the search keyword', $user->email)->contains($search);
            }
        });
    }

    /**
     * Tests whether `User::countProjects()` returns the correct count
     * of all projects owned by a specific user.
     */
    public function testCountProjects()
    {
        $this->specify('User without projects', function() {
            $user = User::findOne(1005);
            verify('Should be empty', $user->countProjects())->equals(0);
        });

        $this->specify('User with projects', function() {
            $user = User::findOne(1003);
            verify('Should count 2 projects', $user->countProjects())->equals(3);
        });
    }

    /**
     * Tests whether `User::countProjects()` returns Project models
     * owned by a specific user based on title keyword search.
     */
    public function testSearchProjects()
    {
        $user = User::findOne(1003);

        $this->specify('Search by non existing project title string', function() use ($user) {
            $projects = $user->searchProjects('non existing title');

            verify('Should be empty', $projects)->count(0);
        });

        $this->specify('Search by existing project title string', function() use ($user) {
            $search   = 'Lorem ipsum';
            $projects = $user->searchProjects($search);

            verify('Should found 2 projects', $projects)->count(2);
            foreach ($projects as $project) {
                verify('Should contains the search keyword', strtoupper($project->title))->contains(strtoupper($search));
            }
        });

        $this->specify('Search by existing project titles string with set limit', function() use ($user) {
            $search   = 'Lorem';
            $projects = $user->searchProjects($search, 1);

            verify('Should found 1 projects', $projects)->count(1);
            foreach ($projects as $project) {
                verify('Should contains the search keyword', strtoupper($project->title))->contains(strtoupper($search));
            }
        });
    }

    /**
     * `User::findProjects()` method test.
     */
    public function testFindProjects()
    {
        $this->specify('User without projects', function() {
            $user     = User::findOne(1005);
            $projects = $user->findProjects();

            verify('Should be empty', $projects)->count(0);
        });

        $this->specify('User with projects', function() {
            $user     = User::findOne(1003);
            $projects = $user->findProjects();
            $validIds = [1002, 1003, 1004];

            verify('Should found 2 projects', $projects)->count(3);
            foreach ($projects as $project) {
                verify('Should be owned by the user', in_array($project->id, $validIds))->true();
            }
        });

        $this->specify('User with projects and set limit', function() {
            $user     = User::findOne(1003);
            $projects = $user->findProjects(1);

            verify('Should found 1 projects', $projects)->count(1);
            verify('Should be owned by the user', $projects[0]->id)->equals(1004); // descendant sort
        });
    }

    /**
     * Tests whether `User::findProjectById()` returns Project model
     * owned by a specific user.
     */
    public function testFindProjectById()
    {
        $user = User::findOne(1002);

        $this->specify('Non existing project', function() use ($user) {
            $project = $user->findProjectById(0);
            verify($project)->null();
        });

        $this->specify('Existing project owned by a different user', function() use ($user) {
            $project = $user->findProjectById(1002);
            verify($project)->null();
        });

        $this->specify('Existing project owned by the current user', function() use ($user) {
            $project = $user->findProjectById(1001);
            verify($project)->isInstanceOf(Project::className());
        });
    }

    /**
     * Tests whether `User::findVersionById()` returns Version model
     * belonging to a project owned by a specific user.
     */
    public function testFindVersionById()
    {
        $user = User::findOne(1002);

        $this->specify('Non existing version', function() use ($user) {
            $version = $user->findVersionById(0);
            verify($version)->null();
        });

        $this->specify('Existing version owned by a different user', function() use ($user) {
            $version = $user->findVersionById(1003);
            verify($version)->null();
        });

        $this->specify('Existing version owned by the current user', function() use ($user) {
            $version = $user->findVersionById(1001);
            verify($version)->isInstanceOf(Version::className());
        });
    }

    /**
     * Tests whether `User::findScreensQuery()` generates valid Screen models query.
     */
    public function testFindScreensQuery()
    {
        $user = User::findOne(1002);

        $this->specify('Valid ActiveQuery object', function() use ($user) {
            $query = $user->findScreensQuery([1001, 1002]);
            verify($query)->isInstanceOf(ActiveQuery::className());
        });

        $this->specify('Non existing screen(s)', function() use ($user) {
            $screens = $user->findScreensQuery(1232456)->all();
            verify($screens)->isEmpty();
        });

        $this->specify('Existing screens owned by a different user', function() use ($user) {
            $screens = $user->findScreensQuery([1003])->all();
            verify($screens)->isEmpty();
        });

        $this->specify('Existing screens owned by the current user', function() use ($user) {
            $screens = $user->findScreensQuery([1001, 1002])->all();
            verify($screens)->count(2);
        });
    }

    /**
     * Tests whether `User::findScreenById()` returns Screen model
     * belonging to a project owned by a specific user.
     */
    public function testFindScreenById()
    {
        $user = User::findOne(1002);

        $this->specify('Non existing screen', function() use ($user) {
            $screen = $user->findScreenById(0);
            verify($screen)->null();
        });

        $this->specify('Existing screen owned by a different user', function() use ($user) {
            $screen = $user->findScreenById(1003);
            verify($screen)->null();
        });

        $this->specify('Existing screen owned by the current user', function() use ($user) {
            $screen = $user->findScreenById(1001);
            verify($screen)->isInstanceOf(Screen::className());
        });
    }

    /**
     * Tests whether `User::findScreenCommentById()` returns ScreenComment model
     * belonging to a screen owned by a specific user.
     */
    public function testFindScreenCommentById()
    {
        $user = User::findOne(1002);

        $this->specify('Non existing comment', function() use ($user) {
            $comment = $user->findScreenCommentById(0);
            verify($comment)->null();
        });

        $this->specify('Existing comment from a screen owned by a different user', function() use ($user) {
            $comment = $user->findScreenCommentById(1004);
            verify($comment)->null();
        });

        $this->specify('Existing comment from a screen owned by the current user', function() use ($user) {
            $comment = $user->findScreenCommentById(1001);
            verify($comment)->isInstanceOf(ScreenComment::className());
        });
    }

    /**
     * Tests whether `User::findLeavedScreenComments()` returns *leaved
     * ScreenComment models belonging to a screen owned by a specific user
     * (*leaved - the comment `from` email is not the same as the user's one).
     */
    public function testFindLeavedScreenComments()
    {
        $this->specify('User WITHOUT leaved comments', function() {
            $user     = User::findOne(1001);
            $comments = $user->findLeavedScreenComments();
            verify($comments)->count(0);
        });

        $this->specify('User WITH leaved comments', function() {
            $user     = User::findOne(1002);
            $comments = $user->findLeavedScreenComments();
            verify($comments)->count(2);
        });
    }

    /**
     * `User::countUnreadCommentsByScreens()` method test.
     */
    public function testCountUnreadCommentsByScreens()
    {
        $this->specify('Count unread comments with not set screens', function() {
            $user          = User::findOne(1004);
            $commentsCount = $user->countUnreadCommentsByScreens([]);

            verify('Should be empty array', $commentsCount)->isEmpty();
        });

        $this->specify('Count unread comments from screens that are NOT owned by the user', function() {
            $user          = User::findOne(1002);
            $commentsCount = $user->countUnreadCommentsByScreens([1004, 6666]);

            verify('Key should exist', $commentsCount)->hasKey(1004);
            verify('Key should exist', $commentsCount)->hasKey(6666);
            verify('Value should be zero', $commentsCount[1004])->equals(0);
            verify('Value should be zero', $commentsCount[6666])->equals(0);
        });

        $this->specify('Count unread comments from screens that are owned by the user', function() {
            $user          = User::findOne(1004);
            $commentsCount = $user->countUnreadCommentsByScreens([1004]);

            verify('Key should exist', $commentsCount)->hasKey(1004);
            verify('Value should match', $commentsCount[1004])->equals(2);
        });

        $this->specify('Count only unread primary comments from screens that are owned by the user', function() {
            $user          = User::findOne(1004);
            $commentsCount = $user->countUnreadCommentsByScreens([1004], true);

            verify('Key should exist', $commentsCount)->hasKey(1004);
            verify('Value should match', $commentsCount[1004])->equals(1);
        });
    }

    /**
     * `User::countUnreadCommentsByProjects()` method test.
     */
    public function testCountUnreadCommentsByProjects()
    {
        $this->specify('Count unread comments with not set projects', function() {
            $user          = User::findOne(1004);
            $commentsCount = $user->countUnreadCommentsByProjects([]);

            verify('Should be empty array', $commentsCount)->isEmpty();
        });

        $this->specify('Count unread comments from projects that are NOT owned by the user', function() {
            $user          = User::findOne(1002);
            $commentsCount = $user->countUnreadCommentsByProjects([1003, 6666]);

            verify('Key should exist', $commentsCount)->hasKey(1003);
            verify('Key should exist', $commentsCount)->hasKey(6666);
            verify('Value should be zero', $commentsCount[1003])->equals(0);
            verify('Value should be zero', $commentsCount[6666])->equals(0);
        });

        $this->specify('Count unread comments from projects that are owned by the user', function() {
            $user          = User::findOne(1004);
            $commentsCount = $user->countUnreadCommentsByProjects([1003]);

            verify('Key should exist', $commentsCount)->hasKey(1003);
            verify('Value should match', $commentsCount[1003])->equals(2);
        });

        $this->specify('Count only unread primary comments from projects that are owned by the user', function() {
            $user          = User::findOne(1004);
            $commentsCount = $user->countUnreadCommentsByProjects([1003], true);

            verify('Key should exist', $commentsCount)->hasKey(1003);
            verify('Value should match', $commentsCount[1003])->equals(1);
        });
    }

    /**
     * Tests whether `User::setSetting()` create/attach a valid UserSetting model.
     */
    public function testSetSetting()
    {
        $user    = User::findOne(1002);
        $result  = $user->setSetting('myTestSetting', 'test');
        $setting = UserSetting::findOne(['settingName' => 'myTestSetting', 'userId' => $user->id]);

        verify('The method should return true', $result)->true();
        verify('UserSetting model to exist', $setting)->isInstanceOf(UserSetting::className());
        verify('UserSetting model to have the specified value', $setting->settingValue)->equals('test');
        verify('UserSetting model to be attached to the user', $setting->userId)->equals($user->id);
    }

    /**
     * Tests whether `User::getSetting()` returns the correct related UserSetting value.
     */
    public function testGetSetting()
    {
        $user = User::findOne(1002);

        $this->specify('Non existing user setting with default value', function() use ($user) {
            $value = $user->getSetting('someNoneExistingSetting', 'defaultMissingValue');
            verify($value)->equals('defaultMissingValue');
        });

        $this->specify('Existing user setting', function() use ($user) {
            $value = $user->getSetting('notifications', false);
            verify($value)->equals(true);
        });
    }

    /* ===============================================================
     * Avatar related methods
     * ============================================================ */
    /**
     * `User::getUploadDir()` method test.
     */
    public function testGetUploadDir()
    {
        $user      = User::findOne(1002);
        $uploadDir = $user->getUploadDir();

        verify('Should not to be empty', $uploadDir)->notEmpty();
        verify('Should begins with the public upload path', $uploadDir)->startsWith(Yii::getAlias('@mainWeb'));
        verify('Should contains a user identifier', $uploadDir)->contains('/' . md5($user->id));
    }

    /**
     * `User::getAvatarPath()` method test.
     */
    public function testGetAvatarPath()
    {
        $user = User::findOne(1002);

        $this->specify('Check thumb path', function() use ($user) {
            $path = $user->getAvatarPath(true);

            verify('Should not to be empty', $path)->notEmpty();
            verify('Should begins with the public upload path', $path)->startsWith(Yii::getAlias('@mainWeb'));
            verify('Should contains a user identifier', $path)->contains('/' . md5($user->id) . '/');
            verify('Should contains avatar file name', $path)->endsWith('/avatar_thumb.jpg');
        });

        $this->specify('Check original path', function() use ($user) {
            $path = $user->getAvatarPath();

            verify('Should not to be empty', $path)->notEmpty();
            verify('Should begins with the public upload path', $path)->startsWith(Yii::getAlias('@mainWeb'));
            verify('Should contains a user identifier', $path)->contains('/' . md5($user->id) . '/');
            verify('Should contains avatar file name', $path)->endsWith('/avatar.jpg');
        });
    }

    /**
     * `User::getAvatarUrl()` method test.
     */
    public function testGetAvatarUrl()
    {
        $user = User::findOne(1002);

        $this->specify('Check thumb path', function() use ($user) {
            $url = $user->getAvatarUrl(true, false);

            verify('Should not to be empty', $url)->notEmpty();
            verify('Should begins with the public upload url', $url)->startsWith(Yii::$app->params['publicUrl']);
            verify('Should contains a user identifier', $url)->contains('/' . md5($user->id) . '/');
            verify('Should contains avatar file name', $url)->endsWith('/avatar_thumb.jpg');
        });

        $this->specify('Check original url', function() use ($user) {
            $url = $user->getAvatarUrl(false, false);

            verify('Should not to be empty', $url)->notEmpty();
            verify('Should begins with the public upload url', $url)->startsWith(Yii::$app->params['publicUrl']);
            verify('Should contains a user identifier', $url)->contains('/' . md5($user->id) . '/');
            verify('Should contains avatar file name', $url)->endsWith('/avatar.jpg');
        });
    }

    /**
     * `User::getTempAvatarPath()` method test.
     */
    public function testGetTempAvatarPath()
    {
        $user = User::findOne(1002);
        $path = $user->getTempAvatarPath();

        verify('Should not to be empty', $path)->notEmpty();
        verify('Should begins with the public upload path', $path)->startsWith(Yii::getAlias('@mainWeb'));
        verify('Should contains a user identifier', $path)->contains('/' . md5($user->id) . '/');
        verify('Should contains temp avatar file name', $path)->endsWith('/avatar_temp.jpg');
    }

    /**
     * `User::getTempAvatarUrl()` method test.
     */
    public function testGetTempAvatarUrl()
    {
        $user = User::findOne(1002);
        $url = $user->getTempAvatarUrl();

        verify('Should not to be empty', $url)->notEmpty();
        verify('Should begins with the public upload url', $url)->startsWith(Yii::$app->params['publicUrl']);
        verify('Should contains a user identifier', $url)->contains('/' . md5($user->id) . '/');
        verify('Should contains temp avatar file name', $url)->endsWith('/avatar_temp.jpg');
    }
}
