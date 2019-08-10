<?php
namespace presentator\api\authclients;

use yii\authclient\OAuth2;
use yii\base\InvalidConfigException;

/**
 * Gitea allows authentication via Gitea OAuth2.
 *
 * In order to use Gitea OAuth2 you must register your application at <https://your-gitea.com/user/settings/security>.
 * Also note that currently Gitea doesn't support scopes <https://github.com/go-gitea/gitea/issues/4300>
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => \yii\authclient\Collection::class,
 *         'clients' => [
 *             'gitlab' => [
 *                 'class'        => \presentator\api\authclients\Gitea::class,
 *                 'domain'       => 'https://your-gitea.com'
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
     * Domain/base url to the Gitea instance.
     *
     * @var string
     */
    public $domain = 'https://try.gitea.io';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$domain` on init.
     */
    public $authUrl = '/login/oauth/authorize';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$domain` on init.
     */
    public $tokenUrl = '/login/oauth/access_token';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$domain` on init.
     */
    public $apiBaseUrl = '/api/v1';

    /**
     * {@inheritdoc}
     */
    public $scope = 'user:email';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->domain) {
            throw new InvalidConfigException('Gitea Oauth2 domain must be set.');
        }

        // normalize props
        $this->domain     = rtrim($this->domain, '/');
        $this->authUrl    = $this->domain . $this->authUrl;
        $this->tokenUrl   = $this->domain . $this->tokenUrl;
        $this->apiBaseUrl = $this->domain . $this->apiBaseUrl;
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
