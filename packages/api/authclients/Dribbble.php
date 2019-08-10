<?php
namespace presentator\api\authclients;

use yii\authclient\OAuth2;

/**
 * Dribbble authclient allows authentication via Dribbble OAuth2.
 *
 * In order to use Dribbble OAuth2 you must register your application at <https://dribbble.com/account/applications/new>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => \yii\authclient\Collection::class,
 *         'clients' => [
 *             'dribbble' => [
 *                 'class'        => \presentator\api\authclients\Dribbble::class,
 *                 'clientId'     => 'dribbble_client_id',
 *                 'clientSecret' => 'dribbble_client_secret',
 *             ],
 *         ],
 *     ],
 *     // ...
 * ]
 * ```
 *
 * @see https://developer.dribbble.com/v1/oauth/
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Dribbble extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://dribbble.com/oauth/authorize';

    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://dribbble.com/oauth/token';

    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://api.dribbble.com/v1';

    /**
     * {@inheritdoc}
     */
    public $scope = 'public';

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
        return 'dribbble';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'Dribbble';
    }
}
