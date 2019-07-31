<?php
namespace presentator\api\base;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Extremely robust and limited Firebase Cloud Firestore REST API component.
 * It is used as an alternative to the default Firebase SDK and doesn't require qRPC extension in order to be used.
 *
 * > Currently there is only a wrapper method for the `upsert` document api action.
 *
 * Example usage:
 * ```php
 * // instantiate (or use it as Yii service component, eg. `Yii::$app->firestore`)
 * $firestore = new Firestore([
 *     'authConfig' => 'path/to/my/auth-config.json',
 *     'projectId'  => 'my-project-id',
 * ]);
 *
 * // upsert a document
 * $response = $firestore->upsert('my-collection', 'my-id', ['title' => ['stringValue' => 'test']]);
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Firestore extends Component
{
    /**
     * Path to your Google service account auth key json file (supports Yii aliases).
     *
     * @see https://cloud.google.com/docs/authentication/production#obtaining_and_providing_service_account_credentials_manually
     * @var string
     */
    public $authConfig = '';

    /**
     * Id of your Firebase project.
     *
     * @var string
     */
    public $projectId = 'presentator';

    /**
     * Firebase Cloud Firestore REST API url.
     *
     * @var string
     */
    public $apiUrl = 'https://firestore.googleapis.com/v1';

    /**
     * @var null|\GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!file_exists(Yii::getAlias($this->authConfig))) {
            throw new InvalidConfigException('Firestore auth config file is missing or invalid.');
        }
    }

    /**
     * Performs Firestore api authorization and creates new http client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function authorizeClient()
    {
        $client = new \Google_Client();

        $client->setAuthConfig(Yii::getAlias($this->authConfig));

        $client->addScope(\Google_Service_Firestore::DATASTORE);

        $this->httpClient = $client->authorize();

        return $this->httpClient;
    }

    /**
     * Upserts a single document.
     * Firestore will create the specified collection and/or entity if doesn't exist.
     * For the appropriate fields format value, please check https://cloud.google.com/firestore/docs/reference/rest/v1/Value#FIELDS-table.
     *
     * Example usage:
     * ```php
     * $response = Yii::$app->firestore->upsert('my-collection', 'my-id', ['title' => ['stringValue' => 'test']]);
     * ```
     *
     * @param  string $collection
     * @param  string $entityId
     * @param  array  $fields
     * @param  array  [$requestOptions] Check http://docs.guzzlephp.org/en/stable/request-options.html
     * @return \GuzzleHttp\Psr7\Response
     */
    public function upsert($collection, $entityId, array $fields, array $requestOptions = [])
    {
        if (!$this->httpClient) {
            $this->authorizeClient();
        }

        $updateMaskQuery = [];
        foreach ($fields as $key => $data) {
            $updateMaskQuery[] = 'updateMask.fieldPaths=' . $key;
        }

        $url = sprintf(
            '%s/projects/%s/databases/(default)/documents/%s/%s?%s',
            rtrim($this->apiUrl, '/'),
            $this->projectId,
            $collection,
            $entityId,
            implode('&', $updateMaskQuery)
        );

        return $this->httpClient->request('PATCH', $url, array_merge([
            'json' => [
                'fields' => $fields,
            ],
        ], $requestOptions));
    }
}
