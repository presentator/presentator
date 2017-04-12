<?php
namespace common\components;

use yii\base\Object;
use yii\filters\auth\AuthInterface;
use yii\web\UnauthorizedHttpException;
use common\models\User;

/**
 * HttpJwtAuth is an action filter that supports HTTP authentication via JWT token.
 *
 * You may use the filter by attaching it as a behavior to a controller or module, like the following:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'jwtAuth' => [
 *             'class' => HttpJwtAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * OR you may use it as part of the composite authentication behavior methods (useful for REST contorllers):
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'authenticator' => [
 *             class' => CompositeAuth::className(),
 *             'authMethods' => [
 *                 HttpJwtAuth::className(),
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HttpJwtAuth extends Object implements AuthInterface
{
    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $token = $request->headers->get('X-Access-Token');
        $identity = User::findByJwtToken($token);

        if ($identity && $user->login($identity)) {
            return $identity;
        }

        $this->handleFailure($response);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
    }
}
