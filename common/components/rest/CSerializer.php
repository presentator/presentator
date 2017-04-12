<?php
namespace common\components\rest;

use Yii;
use yii\rest\Serializer;
use yii\base\Model;
use yii\base\Arrayable;
use yii\data\DataProviderInterface;
use common\components\data\CActiveDataProvider;

/**
 * CSerializer that extends the default yii2 rest Serializer class to
 * normalize and unify the returned data both for single item and collections.
 *
 * It also support custom data enveloping by just setting the `envlope` query param (eg. `?envelope=true`).
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
class CSerializer extends Serializer
{
    /**
     * @inheritdoc
     */
    public $collectionEnvelope = null;

    /**
     * The name of the query parameter based on which the response will be enveloped or not.
     * @var string
     */
    public $envelopeParam = 'envelope';

    /**
     * Helper for the CActiveDataProvider.
     * @var array
     */
    private $fields = [];

    /**
     * Helper for the CActiveDataProvider.
     * @var array
     */
    private $expand = [];

    /**
     * @inheritddoc
     */
    public function serialize($data)
    {
        $result = null;

        if ($data instanceof Model && $data->hasErrors()) {
            $result = $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            $result = $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            $result = $this->serializeDataProvider($data);
        } else {
            $result = $data;
        }

        $requestParam     = $this->request->get($this->envelopeParam, false);
        $envelopeResponse = ($requestParam === 1 || $requestParam === true || $requestParam === 'true');

        if ($envelopeResponse) {
            return [
                'status'   => $this->response->getStatusCode(),
                'headers'  => $this->getFirstHeadersValues(),
                'response' => $result,
            ];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getRequestedFields()
    {
        $requestFields = parent::getRequestedFields();

        if (!empty($this->fields) && is_array($this->fields)) {
            $requestFields[0] = array_merge($requestFields[0], $this->fields);
        }

        if (!empty($this->expand) && is_array($this->expand)) {
            $requestFields[1] = array_merge($requestFields[1], $this->expand);
        }

        return $requestFields;
    }

    /**
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function serializeDataProvider($dataProvider)
    {
        if ($dataProvider instanceof CActiveDataProvider) {
            $this->fields = $dataProvider->fields;
            $this->expand = $dataProvider->expand;
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
     * @return array
     */
    protected function getFirstHeadersValues()
    {
        $result = [];

        $headers = Yii::$app->response->getHeaders()->toArray();
        if (!empty($headers)) {
            foreach ($headers as $name => $value) {
                $result[ucwords($name, '-')] = current($value);
            }
        }

        return $result;
    }
}
