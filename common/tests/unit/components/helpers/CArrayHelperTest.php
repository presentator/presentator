<?php
namespace common\tests\unit\components\helpers;

use common\components\helpers\CArrayHelper;

/**
 * CArrayHelper tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CArrayHelperTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * Checks whether `CArrayHelper::countGroupByKeys()` returns
     * properly formatted array group counter list.
     */
    public function testCountGroupByKeys()
    {
        $group = [
            'group1' => [
                ['value1', 'value2', 'value3'],
                ['value1', 'value2', 'value3'],
            ],
            'group2' => [
                ['value1', 'value2', 'value3'],
            ],
        ];

        $result = CArrayHelper::countGroupByKeys(['group1', 'group2', 'group3'], $group);

        verify('"group1" key should exist', $result)->hasKey('group1');
        verify('"group2" key should exist', $result)->hasKey('group2');
        verify('"group3" key should exist', $result)->hasKey('group3');
        verify('"group1" counter value should match', $result['group1'])->equals(2);
        verify('"group2" counter value should match', $result['group2'])->equals(1);
        verify('"group3" counter value should match', $result['group3'])->equals(0);
    }
}
