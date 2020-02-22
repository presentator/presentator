<?php
namespace presentator\api\rest;

use Yii;
use yii\base\Model;
use yii\base\Arrayable;
use yii\data\DataProviderInterface;
use presentator\api\data\ActiveDataProvider;
use presentator\api\data\ArrayDataProvider;

/**
 * Serializer that extends the default `yii\rest\Serializer` class to
 * normalize and unify the returned data both for single item and collections.
 *
 * It also support custom data enveloping by just setting the `envelope` query parameter (eg. `?envelope=true`).
 * For consistent, the enveloped data contains the following keys:
 * "status"   - Response status code
 * "headers"  - All Response headers
 * "response" - The returned data
 *
 * Sample serialized envelope response:
 * ```json
 * {
 *   "status": 200,
 *   "headers": null,
 *   "response": [
 *     {
 *       "id": 1,
 *       "term": "test-post-1",
 *       "publis_date": "2016-09-27 15:13:31",
 *       "status": 1,
 *       "created_at": "2016-09-27 15:13:31",
 *       "updated_at": "2016-09-27 15:13:31"
 *     },
 *     {
 *       "id": 1,
 *       "term": "test-post-1",
 *       "publis_date": "2016-09-27 15:13:31",
 *       "status": 1,
 *       "created_at": "2016-09-27 15:13:31",
 *       "updated_at": "2016-09-27 15:13:31"
 *     }
 *   ]
 * }
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Serializer extends \yii\rest\Serializer
{
    /**
     * {@inheritdoc}
     */
    public $collectionEnvelope = null;

    /**
     * The name of the query parameter based on which the response will be enveloped or not.
     *
     * @var string
     */
    public $envelopeParam = 'envelope';

    /**
     * Whether to allow specifying `fields` as request query parameter.
     *
     * @var bool
     */
    public $allowRequestFields = true;

    /**
     * Whether to allow specifying `expand` as request query parameter.
     *
     * @var bool
     */
    public $allowRequestExpand = true;

    /**
     * Helper for the `ArrayDataProvider` and `ActiveDataProvider`.
     *
     * @var array
     */
    private $fields = [];

    /**
     * Helper for the `ArrayDataProvider` and `ActiveDataProvider`.
     *
     * @var array
     */
    private $expand = [];

    /**
     * {@inheritdoc}
     */
    public function serialize($data)
    {
        $result = parent::serialize($data);

        $envelopeParam = $this->request->get($this->envelopeParam, false);
        if ($envelopeParam == 1 || $envelopeParam === 'true') {
            return [
                'status'   => $this->response->getStatusCode(),
                'headers'  => $this->getFirstHeadersValues(),
                'response' => $result,
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestedFields()
    {
        $requestFields = parent::getRequestedFields();

        if (!$this->allowRequestFields) {
            $requestFields[0] = [];
        }

        if (!$this->allowRequestExpand) {
            $requestFields[1] = [];
        }

        if (!empty($this->fields) && is_array($this->fields)) {
            $requestFields[0] = array_merge($requestFields[0], $this->fields);
        }

        if (!empty($this->expand) && is_array($this->expand)) {
            $requestFields[1] = array_merge($requestFields[1], $this->expand);
        }

        return $requestFields;
    }

    /**
     * {@inheritdoc}
     */
    protected function serializeDataProvider($dataProvider)
    {
        if (
            ($dataProvider instanceof ActiveDataProvider) ||
            ($dataProvider instanceof ArrayDataProvider)
        ) {
            $this->fields = $dataProvider->fields;
            $this->expand = $dataProvider->expand;

            $this->allowRequestFields = $dataProvider->allowRequestFields;
            $this->allowRequestExpand = $dataProvider->allowRequestExpand;

        }

        $models = $this->serializeModels($dataProvider->getModels());

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return null;
        }

        return $models;
    }

    /**
     * Returns a list with only the first value of each header collection.
     *
     * @return array
     */
    protected function getFirstHeadersValues(): array
    {
        $result  = [];
        $headers = Yii::$app->response->getHeaders()->toArray();

        if (!empty($headers)) {
            foreach ($headers as $name => $value) {
                $result[strtolower($name)] = current($value);
            }
        }

        return $result;
    }
}
