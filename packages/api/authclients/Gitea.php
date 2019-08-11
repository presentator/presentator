<?php
namespace presentator\api\authclients;

use yii\authclient\OAuth2;
use yii\base\InvalidConfigException;

/**
 * Gitea authclient allows authentication via Gitea OAuth2.
 *
 * In order to use Gitea OAuth2 you must register your application at <https://your-gitea.com/user/settings/security>.
 * Also note that currently Gitea doesn't support scopes <https://github.com/go-gitea/gitea/issues/4300>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => \yii\authclient\Collection::class,
 *         'clients' => [
 *             'gitea' => [
 *                 'class'        => \presentator\api\authclients\Gitea::class,
 *                 'serviceUrl'   => 'https://your-gitea.com'
 *                 'clientId'     => 'gitea_client_id',
 *                 'clientSecret' => 'gitea_client_secret',
 *             ],
 *         ],
 *     ],
 *     // ...
 * ]
 * ```
 *
 * @see https://docs.gitea.io/en-us/oauth2-provider/
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Gitea extends OAuth2
{
    /**
     * Base url to your Gitea instance.
     *
     * @var string
     */
    public $serviceUrl = '';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$serviceUrl` on init.
     */
    public $authUrl = '/login/oauth/authorize';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$serviceUrl` on init.
     */
    public $tokenUrl = '/login/oauth/access_token';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$serviceUrl` on init.
     */
    public $apiBaseUrl = '/api/v1';

    /**
     * {@inheritdoc}
     *
     * _Scopes are not supported yet - <https://github.com/go-gitea/gitea/issues/4300>_
     */
    public $scope = 'user:email';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->serviceUrl) {
            throw new InvalidConfigException('Gitea authclient serviceUrl must be set.');
        }

        // normalize props
        $this->serviceUrl = rtrim($this->serviceUrl, '/');
        $this->authUrl    = $this->serviceUrl . $this->authUrl;
        $this->tokenUrl   = $this->serviceUrl . $this->tokenUrl;
        $this->apiBaseUrl = $this->serviceUrl . $this->apiBaseUrl;
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('user', 'GET');
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'gitea';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Gitea';
    }
}
