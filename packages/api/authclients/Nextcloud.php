<?php
namespace presentator\api\authclients;

use yii\authclient\OAuth2;
use yii\base\InvalidConfigException;

/**
 * Nextcloud authclient allows authentication via Nextcloud OAuth2.
 *
 * Check <https://docs.nextcloud.com/server/14/admin_manual/configuration_server/oauth2.html> for registering an OAuth2 app.
 * Also note that currently Nextcloud doesn't support scopes.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => \yii\authclient\Collection::class,
 *         'clients' => [
 *             'nextcloud' => [
 *                 'class'        => \presentator\api\authclients\Nextcloud::class,
 *                 'serviceUrl'   => 'https://your-nextcloud.com'
 *                 'clientId'     => 'nextcloud_client_id',
 *                 'clientSecret' => 'nextcloud_client_secret',
 *             ],
 *         ],
 *     ],
 *     // ...
 * ]
 * ```
 *
 * @see https://docs.nextcloud.com/server/14/admin_manual/configuration_server/oauth2.html
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Nextcloud extends OAuth2
{
    /**
     * Base url to your NextCloud instance.
     *
     * @var string
     */
    public $serviceUrl = '';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$serviceUrl` on init.
     */
    public $authUrl = '/apps/oauth2/authorize';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$serviceUrl` on init.
     */
    public $tokenUrl = '/apps/oauth2/api/v1/token';

    /**
     * {@inheritdoc}
     *
     * Will be auto prefixed with `$serviceUrl` on init.
     */
    public $apiBaseUrl = '/ocs/v2.php';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->serviceUrl) {
            throw new InvalidConfigException('Nextcloud authclient serviceUrl must be set.');
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
        $fields = $this->api('cloud/user', 'GET', [
            'format' => 'json',
        ]);

        return $fields['ocs']['data'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'nextcloud';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Nextcloud';
    }
}
