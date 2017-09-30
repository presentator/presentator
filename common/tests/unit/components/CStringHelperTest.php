<?php
namespace common\tests\unit\components;

use common\components\helpers\CStringHelper;

/**
 * CStringHelper tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CStringHelperTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `CStringHelper::autoTypecast()` method test.
     */
    public function testAutoTypecast()
    {
        $this->specify('Type cast to int', function() {
            verify(CStringHelper::autoTypecast(123))->equals(123);
            verify(CStringHelper::autoTypecast('456'))->equals(456);
        });

        $this->specify('Type cast to float', function() {
            verify(CStringHelper::autoTypecast(123.5))->equals(123.5);
            verify(CStringHelper::autoTypecast('456.5'))->equals(456.5);
        });

        $this->specify('Type cast to null', function() {
            verify(CStringHelper::autoTypecast(null))->equals(null);
            verify(CStringHelper::autoTypecast('null'))->equals(null);
        });

        $this->specify('Type cast to boolean', function() {
            verify(CStringHelper::autoTypecast(true))->equals(true);
            verify(CStringHelper::autoTypecast(false))->equals(false);
            verify(CStringHelper::autoTypecast('true'))->equals(true);
            verify(CStringHelper::autoTypecast('false'))->equals(false);
        });

        $this->specify('Type cast to string', function() {
            verify(CStringHelper::autoTypecast('test'))->equals('test');
        });
    }

    /**
     * `CStringHelper::parseAddresses()` method test.
     */
    public function testParseAddresses()
    {
        $this->specify('Parse an empty addresses list string', function() {
            $result = CStringHelper::parseAddresses('');

            verify('No addresses should be found', $result)->equals([]);
        });

        $this->specify('Parse single address with name', function() {
            $result = CStringHelper::parseAddresses('John Doe <john.doe@presentator.io>');

            verify('Addresses count should match', $result)->count(1);
            verify('Addresses count should match', $result)->hasKey('john.doe@presentator.io');
            verify('Addresses count should match', $result['john.doe@presentator.io'])->equals('John Doe');
        });

        $this->specify('Parse single address without name', function() {
            $result = CStringHelper::parseAddresses('test@presentator.io');

            verify('Addresses count should match', $result)->count(1);
            verify('Addresses count should match', $result)->hasKey('test@presentator.io');
            verify('Addresses count should match', $result['test@presentator.io'])->equals(null);
        });

        $this->specify('Parse addresses list string with 2_ addresses', function() {
            $result = CStringHelper::parseAddresses('John Doe <john.doe@presentator.io>, test@presentator.io');

            verify('Addresses count should match', $result)->count(2);
            verify('Addresses count should match', $result)->hasKey('john.doe@presentator.io');
            verify('Addresses count should match', $result)->hasKey('test@presentator.io');
            verify('Addresses count should match', $result['john.doe@presentator.io'])->equals('John Doe');
            verify('Addresses count should match', $result['test@presentator.io'])->equals(null);
        });
    }
}
