<?php
namespace common\components\data;

use yii\data\Sort;
use yii\data\ActiveDataProvider;

/**
 * Custom active data provider that enhance the default yii one by
 * adding options to set manually fields and expand model fields and other stuffs.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CActiveDataProvider extends ActiveDataProvider
{
    /**
     * @var array
     */
    public $fields = [];

    /**
     * @var array
     */
    public $expand = [];

    /**
     * @var array
     */
    public $order = [];

    /**
     * @var null|yii\data\Sort
     */
    private $sortObj = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!empty($this->order)) {
            $sort = $this->getSortObj();
            $sort->defaultOrder = $this->order;

            $this->setSort($sort);
        }
    }

    /**
     * @param yii\data\Sort
     */
    public function getSortObj()
    {
        if (!$this->sortObj) {
            $this->sortObj = new Sort();
        }

        return $this->sortObj;
    }
}
