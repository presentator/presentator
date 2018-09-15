<?php
namespace common\components;

use yii\authclient\OAuth2;

/**
 * GitLab allows authentication via GitLab OAuth.
 *
 * In order to use GitLab OAuth you must register your application at <https://gitlab.com/profile/applications>.
 *
 * Example application configuration:
 *
 * ```php
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => yii\authclient\Collection::class,
 *         'clients' => [
 *             'gitlab' => [
 *                 'class' => \yiiauth\gitlab\GitLabClient::class,
 *                 'domain'  => 'https://gitlab.com'
 *                 'clientId' => 'gitlab_client_id',
 *                 'clientSecret' => 'gitlab_client_secret',
 *             ],
 *         ],
 *     ]
 *     // ...
 * ]
 * ```
 *
 * @see    https://docs.gitlab.com/ee/api/oauth2.html
 * @see    https://gitlab.com/profile/applications
 *
 * @author Dmitriy Kuts <me@exileed.com>
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GitLabAuthClient extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    public $authUrl = '/oauth/authorize';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = '/oauth/token';
    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = '/api/v4/';
    /**
     * {@inheritdoc}
     */
    public $scope = 'read_user';
    /**
     * Domain instance gitlab
     * @var string
     */
    public $domain = 'https://gitlab.com';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->authUrl    = $this->domain.$this->authUrl;
        $this->tokenUrl   = $this->domain.$this->tokenUrl;
        $this->apiBaseUrl = $this->domain.$this->apiBaseUrl;
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
        return 'gitlab';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return 'GitLab';
    }
}
