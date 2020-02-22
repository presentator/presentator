<?php
namespace presentator\api\data;

/**
 * Defines helper fields that are usually used by `presentator\api\rest\Serializer`
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
trait RestDataProviderTrait
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
     * Tells the serialzer whether to allow merging request's `fields`
     * parameter with the provider's `fields`.
     *
     * @var bool
     */
    public $allowRequestFields = true;

    /**
     * Tells the serialzer whether to allow merging request's `expand`
     * parameter with the provider's `expand`.
     *
     * @var bool
     */
    public $allowRequestExpand = true;
}
