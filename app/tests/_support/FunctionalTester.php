<?php
namespace app\tests;

use Yii;
use common\models\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions {
        seeCurrentUrlEquals     as public traitSeeCurrentUrlEquals;
        dontSeeCurrentUrlEquals as public traitDontSeeCurrentUrlEquals;
        sendAjaxGetRequest      as public traitSendAjaxGetRequest;
        sendAjaxPostRequest     as public traitSendAjaxPostRequest;
        sendAjaxRequest         as public traitSendAjaxRequest;
    }

    /**
     * Returns url with supports for Yii2 route configurations.
     * @param string|array $uri
     * @param boolean $absolute
     * @return string
     */
    protected function buildUrl($uri, $absolute = false)
    {
        if (is_array($uri)) {
            if ($absolute) {
                return Yii::$app->urlManager->createAbsoluteUrl($uri);
            } else {
                return Yii::$app->urlManager->createUrl($uri);
            }
        }

        return $uri;
    }

    /**
     * Override `FunctionalTesterActions::seeCurrentUrlEquals` to support dynamic url routes.
     * @param string|array $uri
     * @param boolean      $absolute
     * @see `_generated\FunctionalTesterActions::seeCurrentUrlEquals()`
     */
    public function seeCurrentUrlEquals($uri, $absolute = false)
    {
        return $this->traitSeeCurrentUrlEquals($this->buildUrl($uri, $absolute));
    }

    /**
     * Override `FunctionalTesterActions::dontSeeCurrentUrlEquals` to support dynamic url routes.
     * @param string|array $uri
     * @param boolean      $absolute
     * @see `_generated\FunctionalTesterActions::dontSeeCurrentUrlEquals()`
     */
    public function dontSeeCurrentUrlEquals($uri, $absolute = false)
    {
        return $this->traitDontSeeCurrentUrlEquals($this->buildUrl($uri, $absolute));
    }

    /**
     * Override `FunctionalTesterActions::sendAjaxGetRequest` to support dynamic url routes.
     * @param string|array $uri
     * @param array        $params
     * @param boolean      $absolute
     * @see `_generated\FunctionalTesterActions::sendAjaxGetRequest()`
     */
    public function sendAjaxGetRequest($uri, $params = [], $absolute = false)
    {
        return $this->traitSendAjaxGetRequest($this->buildUrl($uri, $absolute), $params);
    }

    /**
     * Override `FunctionalTesterActions::sendAjaxPostRequest` to support dynamic url routes.
     * @param string|array $uri
     * @param array        $params
     * @param boolean      $absolute
     * @see `_generated\FunctionalTesterActions::sendAjaxPostRequest()`
     */
    public function sendAjaxPostRequest($uri, $params = [], $absolute = false)
    {
        return $this->traitSendAjaxPostRequest($this->buildUrl($uri, $absolute), $params);
    }

    /**
     * Override `FunctionalTesterActions::sendAjaxRequest` to support dynamic url routes.
     * @param string       $method
     * @param string|array $uri
     * @param array        $params
     * @param boolean      $absolute
     * @see `_generated\FunctionalTesterActions::sendAjaxRequest()`
     */
    public function sendAjaxRequest($method, $uri, $params = [], $absolute = false)
    {
        return $this->traitSendAjaxRequest($method, $this->buildUrl($uri, $absolute), $params);
    }

    /**
     * Tests whether a guest can access user restricted route.
     * @param string|array $uri
     */
    public function canAccessAsGuest($uri)
    {
        $loggedUser = null;
        if (!Yii::$app->user->isGuest) {
            $loggedUser = Yii::$app->user->identity;
            Yii::$app->user->logout();
        }

        $this->amOnPage($uri);
        $this->seeResponseCodeIs(200);
        $this->expectTo('stay on the same page');
        $this->seeCurrentUrlEquals($uri);

        if ($loggedUser) {
            Yii::$app->user->login($loggedUser, 0);
        }
    }

    /**
     * Tests whether a guest can access user restricted route.
     * @param string|array $uri
     */
    public function cantAccessAsGuest($uri)
    {
        $loggedUser = null;
        if (!Yii::$app->user->isGuest) {
            $loggedUser = Yii::$app->user->identity;
            Yii::$app->user->logout();
        }

        $this->amOnPage($uri);
        $this->seeResponseCodeIs(200);
        $this->expectTo('redirect to the login page');
        $this->seeCurrentUrlEquals(['site/entrance']);

        if ($loggedUser) {
            Yii::$app->user->login($loggedUser, 0);
        }
    }

    /**
     * Tests whether a regular user can access super user restricted route.
     * @param string|array $uri
     */
    public function cantAccessAsRegularUser($uri)
    {
        // logout
        $loggedUser = null;
        if (!Yii::$app->user->isGuest) {
            $loggedUser = Yii::$app->user->identity;
            Yii::$app->user->logout();
        }

        $regularUser = User::findOne(['type' => User::TYPE_REGULAR, 'status' => User::STATUS_ACTIVE]);
        $this->amLoggedInAs($regularUser);
        $this->amOnPage($uri);
        $this->seeResponseCodeIs(200);
        $this->expectTo('redirect to the login page');
        $this->seeCurrentUrlEquals(['site/index']);

        // reset
        if ($loggedUser) {
            Yii::$app->user->login($loggedUser, 0);
        }
    }

    /**
     * Tests whether a super user can access regular user only route.
     * @param string|array $uri
     */
    public function cantAccessAsSuperUser($uri)
    {
        // logout
        $loggedUser = null;
        if (!Yii::$app->user->isGuest) {
            $loggedUser = Yii::$app->user->identity;
            Yii::$app->user->logout();
        }

        $superUser = User::findOne(['type' => User::TYPE_SUPER, 'status' => User::STATUS_ACTIVE]);
        $this->amLoggedInAs($superUser);
        $this->amOnPage($uri);
        $this->seeResponseCodeIs(200);
        $this->expectTo('redirect to the login page');
        $this->seeCurrentUrlEquals(['site/index']);

        // reset
        if ($loggedUser) {
            Yii::$app->user->login($loggedUser, 0);
        }
    }

    /**
     * Helper actor that contains couple of tests to ensure that an action
     * allows only ajax GET requests from authenticated users.
     * @param string|array $uri
     * @param array        $params
     * @param boolean      $allowGuests
     * @param null|integer $onlyUserType
     */
    public function ensureAjaxGetActionAccess($uri, array $params = [], $allowGuests = false, $onlyUserType = null)
    {
        if (!$allowGuests) {
            $this->amGoingTo('ensure that guests cannot access the action');
            $this->cantAccessAsGuest($uri);

            if ($onlyUserType === User::TYPE_SUPER) {
                $this->cantAccessAsRegularUser($uri);
            } elseif ($onlyUserType === User::TYPE_REGULAR) {
                $this->cantAccessAsSuperUser($uri);
            }
        }

        $this->amGoingTo('try send a non-ajax request');
        $this->amOnPage($uri, $params);
        $this->seeResponseCodeIs(400);

        $this->amGoingTo('try send an ajax GET request');
        $this->sendAjaxGetRequest($uri, $params);
        $this->seeResponseCodeIs(200);
    }

    /**
     * Helper actor that contains couple of tests to ensure that an action
     * allows only ajax POST requests from authenticated users.
     * @param string|array $uri
     * @param array        $params
     * @param boolean      $allowGuests
     * @param null|integer $onlyUserType
     */
    public function ensureAjaxPostActionAccess($uri, array $params = [], $allowGuests = false, $onlyUserType = null)
    {
        if (!$allowGuests) {
            $this->amGoingTo('ensure that guests cannot access the action');
            $this->cantAccessAsGuest($uri);

            if ($onlyUserType === User::TYPE_SUPER) {
                $this->cantAccessAsRegularUser($uri);
            } elseif ($onlyUserType === User::TYPE_REGULAR) {
                $this->cantAccessAsSuperUser($uri);
            }
        }

        $this->amGoingTo('try send an ajax GET request');
        $this->sendAjaxGetRequest($uri, $params);
        $this->seeResponseCodeIs(405);

        $this->amGoingTo('try send a non-ajax POST request');
        $this->sendPOST($uri, $params);
        $this->seeResponseCodeIs(400);

        $this->amGoingTo('try send an ajax POST request');
        $this->sendAjaxPostRequest($uri, $params);
        $this->seeResponseCodeIs(200);
    }
}
