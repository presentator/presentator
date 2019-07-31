<?php
namespace presentator\api\filters;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\UnauthorizedHttpException;

/**
 * HttpJwtAuth is an action filter that supports HTTP authentication via JWT token.
 *
 * You may use the filter by attaching it as a behavior to a controller or module, like the following:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'jwtAuth' => [
 *             'class' => HttpJwtAuth::className,
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
 *             class' => CompositeAuth::className,
 *             'authMethods' => [
 *                 HttpJwtAuth::className,
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HttpJwtAuth extends HttpBearerAuth
{
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if (
            $authHeader === null ||
            !preg_match($this->pattern, $authHeader, $matches)
        ) {
            return null;
        }

        $token    = $matches[1] ?? '';
        $identity = null;

        if ($token) {
            $identity = $user->loginByAccessToken($token, get_class($this));
        }

        if (!$identity) {
            $this->challenge($response);
            $this->handleFailure($response);
        }

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('Invalid or expired authorization token.');
    }
}
