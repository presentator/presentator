<?php
namespace presentator\api\tests\functional;

use presentator\api\tests\FunctionalTester;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\tests\fixtures\ScreenFixture;
use presentator\api\tests\fixtures\ScreenCommentFixture;
use presentator\api\tests\fixtures\UserFixture;
use presentator\api\tests\fixtures\UserProjectRelFixture;
use presentator\api\tests\fixtures\UserScreenCommentRelFixture;
use presentator\api\models\User;
use presentator\api\models\ScreenComment;
use presentator\api\models\UserScreenCommentRel;

/**
 * ScreenCommentsController API functional tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentsCest
{
    /**
     * {@inheritdoc}
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'ProjectFixture' => [
                'class' => ProjectFixture::class,
            ],
            'PrototypeFixture' => [
                'class' => PrototypeFixture::class,
            ],
            'ScreenFixture' => [
                'class' => ScreenFixture::class,
            ],
            'ScreenCommentFixture' => [
                'class' => ScreenCommentFixture::class,
            ],
            'UserFixture' => [
                'class' => UserFixture::class,
            ],
            'UserProjectRelFixture' => [
                'class' => UserProjectRelFixture::class,
            ],
            'UserScreenCommentRelFixture' => [
                'class' => UserScreenCommentRelFixture::class,
            ],
        ]);
    }

    /* `ScreenCommentsController::actionIndex()`
    --------------------------------------------------------------- */
    /**
     * `ScreenCommentsController::actionIndex()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function indexFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list screen comments');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/screen-comments');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `ScreenCommentsController::actionIndex()` success test.
     *
     * @param FunctionalTester $I
     */
    public function indexSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list screen comments');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $scenarioCallback = function ($scenarioIndex, $scenarioData) use ($I) {
            if (!empty($scenarioData['expected'])) {
                $I->seeResponseMatchesJsonType([
                    'id'       => 'integer',
                    'fromUser' => 'null|array',
                ]);
                $I->dontSeeResponseContainsUserHiddenFields('fromUser');
            }
        };

        $I->amGoingTo('authorize as regular user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/screen-comments', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003, 1004, 1005],
            ],
            [
                'params'   => ['per-page' => 1, 'page' => 2],
                'expected' => [1002],
            ],
            [
                'params'   => ['search[screenId]' => 1001],
                'expected' => [1001, 1002],
            ],
            [
                'params'   => ['search[prototypeId]' => 1003],
                'expected' => [1004, 1005],
            ],
            [
                'params'   => ['search[prototypeId]' => 1005],
                'expected' => [],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1005, 1004, 1003, 1002, 1001],
            ],
        ], $scenarioCallback);

        $I->amGoingTo('authorize as super user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendAndCheckDataProviderResponses('/screen-comments', [
            [
                'params'   => [],
                'expected' => [1001, 1002, 1003, 1004, 1005, 1006],
            ],
            [
                'params'   => ['per-page' => 2, 'page' => 2],
                'expected' => [1003, 1004],
            ],
            [
                'params'   => ['search[screenId]' => 1007],
                'expected' => [1006],
            ],
            [
                'params'   => ['sort' => '-createdAt'],
                'expected' => [1006, 1005, 1004, 1003, 1002, 1001],
            ],
        ], $scenarioCallback);
    }

    /* `ScreenCommentsController::actionCreate()`
    --------------------------------------------------------------- */
    /**
     * `ScreenCommentsController::actionCreate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function createFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully create new screen comment');

        $user = User::findOne(1002);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPOST('/screen-comments');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize and submit invalid form data');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendPOST('/screen-comments', [
            'screenId' => 1007,
            'replyTo'  => 1006,
            'message'  => '',
            'left'     => -10,
            'top'      => -10,
            'status'   => 'invalid',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'screenId' => 'string',
                'replyTo'  => 'string',
                'message'  => 'string',
                'left'     => 'string',
                'top'      => 'string',
                'status'   => 'string',
            ],
        ]);
    }

    /**
     * `ScreenCommentsController::actionCreate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function createSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully create new screen comment');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment' => 'authorize as regular user and create a new screen comment for an owned project',
                'token'   => $regularUser->generateAccessToken(),
                'data'    => [
                    'screenId' => 1002,
                    'replyTo'  => null,
                    'message'  => 'test_create +test@example.com',
                    'left'     => 100,
                    'top'      => 0,
                    'status'   => 'resolved',
                ],
                'expectedEmailsCount' => 1, // mention email
            ],
            [
                'comment' => 'authorize as super user and create a new screen comment for a project',
                'token'   => $superUser->generateAccessToken(),
                'data'    => [
                    'screenId' => 1007,
                    'replyTo'  => 1006,
                    'message'  => 'test_create2 +missing@example.com',
                    'left'     => 0,
                    'top'      => 0,
                ],
                'expectedEmailsCount' => 0, // unexisting collaborator
            ],
        ];

        $totalExpectedEmails = 0;
        foreach ($testScenarios as $scenario) {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPOST('/screen-comments', $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'screenId' => 'integer',
                'replyTo'  => 'integer|null',
                'message'  => 'string',
                'left'     => 'integer|float',
                'top'      => 'integer|float',
                'fromUser' => 'array',
            ]);
            $I->seeResponseContainsJson($scenario['data']);
            $I->dontSeeResponseContainsUserHiddenFields('fromUser');

            $totalExpectedEmails += $scenario['expectedEmailsCount'];
            $I->seeEmailIsSent($totalExpectedEmails);
        }
    }

    /* `ScreenCommentsController::actionUpdate()`
    --------------------------------------------------------------- */
    /**
     * `ScreenCommentsController::actionUpdate()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function updateFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully update screen comment');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/screen-comments/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to update screen comment owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/screen-comments/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and submit invalid form data to a random screen comment');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/screen-comments/1006', [
            'screenId' => 123456,    // should be ignored
            'replyTo'  => 123456,    // should be ignored
            'message'  => '',        // should be ignored
            'from'     => 'invalid', // should be ignored
            'left'     => -10,
            'top'      => -10,
            'status'   => 'invalid',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => [
                'left'   => 'string',
                'top'    => 'string',
                'status' => 'string',
            ],
        ]);
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.screenId');
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.replyTo');
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.message');
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.from');
    }

    /**
     * `ScreenCommentsController::actionUpdate()` success test.
     *
     * @param FunctionalTester $I
     */
    public function updateSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully update screen comment');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and update owned screen comment',
                'token'     => $regularUser->generateAccessToken(),
                'commentId' => 1001,
                'data'      => [
                    'left'   => 10.10,
                    'top'    => 20.20,
                    'status' => ScreenComment::STATUS['RESOLVED'],
                ],
            ],
            [
                'comment'   => 'authorize as super user and update a screen comment',
                'token'     => $superUser->generateAccessToken(),
                'commentId' => 1006,
                'data'      => [
                    'status' => ScreenComment::STATUS['RESOLVED'],
                ],
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $originalComment = ScreenComment::findOne($scenario['commentId']);

            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendPUT('/screen-comments/' . $scenario['commentId'], $scenario['data']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseMatchesJsonType([
                'id'       => ('integer:=' . $scenario['commentId']),
                'replyTo'  => 'integer|null',
                'message'  => 'string',
                'left'     => 'integer|float',
                'top'      => 'integer|float',
                'fromUser' => 'array|null',
            ]);
            $I->seeResponseContainsJson($scenario['data']);
            $I->seeResponseContainsJson(['from' => $originalComment->from]);
            $I->dontSeeResponseContainsUserHiddenFields('fromUser');
        }
    }

    /* `ScreenCommentsController::actionView()`
    --------------------------------------------------------------- */
    /**
     * `ScreenCommentsController::actionView()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function viewFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully view screen comment');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/screen-comments/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to view screen comment owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendGET('/screen-comments/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to view unexisting screen comment');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendGET('/screen-comments/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ScreenCommentsController::actionView()` success test.
     *
     * @param FunctionalTester $I
     */
    public function viewSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully view screen comment');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to view owned screen comment',
                'token'     => $regularUser->generateAccessToken(),
                'commentId' => 1001,
            ],
            [
                'comment'   => 'authorize as super user and try to view a screen comment',
                'token'     => $superUser->generateAccessToken(),
                'commentId' => 1004,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendGET('/screen-comments/' . $scenario['commentId']);
            $I->seeResponseCodeIs(200);
            $I->seeResponseIsJson();
            $I->seeResponseMatchesJsonType([
                'id'       => 'integer:=' . $scenario['commentId'],
                'fromUser' => 'null|array',
            ]);
            $I->dontSeeResponseContainsUserHiddenFields('fromUser');
        }
    }

    /* `ScreenCommentsController::actionDelete()`
    --------------------------------------------------------------- */
    /**
     * `ScreenCommentsController::actionDelete()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function deleteFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully delete screen comment');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendDELETE('/screen-comments/1006');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to delete screen comment owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendDELETE('/screen-comments/1006');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to delete unexisting screen comment');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendDELETE('/screen-comments/123456');
        $I->seeNotFoundResponse();
    }

    /**
     * `ScreenCommentsController::actionDelete()` success test.
     *
     * @param FunctionalTester $I
     */
    public function deleteSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully delete screen comment');

        $regularUser  = User::findOne(1002);
        $superUser    = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and try to delete an owned screen comment',
                'token'     => $regularUser->generateAccessToken(),
                'commentId' => 1002,
            ],
            [
                'comment'   => 'authorize as super user and try to delete a screen comment',
                'token'     => $superUser->generateAccessToken(),
                'commentId' => 1006,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['token']);
            $I->sendDELETE('/screen-comments/' . $scenario['commentId']);
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(ScreenComment::class, ['id' => $scenario['commentId']]);
        }
    }

    /* `ScreenCommentsController::actionListUnread()`
    --------------------------------------------------------------- */
    /**
     * `ScreenCommentsController::actionListUnread()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function listUnreadFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully list unread screen comments');

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendGET('/screen-comments/unread');
        $I->seeUnauthorizedResponse();
    }

    /**
     * `ScreenCommentsController::actionListUnread()` success test.
     *
     * @param FunctionalTester $I
     */
    public function listUnreadSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully list unread screen comments');

        $user             = User::findOne(1002);
        $expectedComments = $user->findUnreadScreenComments();

        $I->amGoingTo('authorize and list all user\'s unread screen comments');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $user->generateAccessToken());
        $I->sendGET('/screen-comments/unread');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'id'       => 'integer',
            'metaData' => 'array',
            'fromUser' => 'null|array',
        ]);
        $I->dontSeeResponseContainsUserHiddenFields('*.fromUser');

        foreach ($expectedComments as $comment) {
            $I->seeResponseContainsJson([['id' => $comment->id]]);
        }
    }

    /* `ScreenCommentsController::actionRead()`
    --------------------------------------------------------------- */
    /**
     * `ScreenCommentsController::actionRead()` failure test.
     *
     * @param FunctionalTester $I
     */
    public function readFailure(FunctionalTester $I)
    {
        $I->wantTo('Unsuccessfully mark a comment as read');

        $regularUser = User::findOne(1002);
        $superUser   = User::findOne(['status' => User::STATUS['ACTIVE'], 'type' => User::TYPE['SUPER']]);

        $I->amGoingTo('try accessing the action unauthorized');
        $I->sendPUT('/screen-comments/1006/read');
        $I->seeUnauthorizedResponse();

        $I->amGoingTo('authorize as regular user and try to read screen comment owned by another user');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $regularUser->generateAccessToken());
        $I->sendPUT('/screen-comments/1006/read');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to read unexisting screen comment');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/screen-comments/123456/read');
        $I->seeNotFoundResponse();

        $I->amGoingTo('authorize as super user and try to read unnotified screen comment');
        $I->haveHttpHeader('Authorization', 'Bearer ' . $superUser->generateAccessToken());
        $I->sendPUT('/screen-comments/1003/read');
        $I->seeBadRequestResponse();
    }

    /**
     * `ScreenCommentsController::actionRead()` success test.
     *
     * @param FunctionalTester $I
     */
    public function readSuccess(FunctionalTester $I)
    {
        $I->wantTo('Successfully mark a comment as read');

        $user  = User::findOne(1002);

        $testScenarios = [
            [
                'comment'   => 'authorize as regular user and read already read screen comment',
                'user'      => $user,
                'commentId' => 1003,
            ],
            [
                'comment'   => 'authorize as regular user and read unread screen comment',
                'user'      => $user,
                'commentId' => 1001,
            ],
        ];

        foreach ($testScenarios as $scenario) {
            $I->amGoingTo($scenario['comment']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $scenario['user']->generateAccessToken());
            $I->sendPUT("/screen-comments/{$scenario['commentId']}/read");
            $I->seeResponseCodeIs(204);
            $I->dontSeeRecord(UserScreenCommentRel::class, [
                'userId'          => $scenario['user']->id,
                'screenCommentId' => $scenario['commentId'],
                'isRead'          => 0,
            ]);
        }
    }
}
