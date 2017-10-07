<?php
namespace common\tests\unit\components\helpers;

use common\components\helpers\EmailHelper;

/**
 * EmailHelper tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class EmailHelperTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `EmailHelper::stringToArray()` method test.
     */
    public function testStringToArray()
    {
        $this->specify('Parse an empty addresses list string', function() {
            $result = EmailHelper::stringToArray('');

            verify('No addresses should be parsed', $result)->equals([]);
        });

        $this->specify('Parse single email address with name', function() {
            $result = EmailHelper::stringToArray('John Doe <john.doe@presentator.io>');

            verify('Should results in only one parsed email address', $result)->count(1);
            verify('Address email should exist as a result key', $result)->hasKey('john.doe@presentator.io');
            verify('Address name should match', $result['john.doe@presentator.io'])->equals('John Doe');
        });

        $this->specify('Parse single email address without name', function() {
            $result = EmailHelper::stringToArray('test@presentator.io');

            verify('Should results in only one parsed email address', $result)->count(1);
            verify('Address email should exist as a result key', $result)->hasKey('test@presentator.io');
            verify('Address name should not be set', $result['test@presentator.io'])->null();
        });

        $this->specify('Parse email addresses list string with 1+ addresses', function() {
            $result = EmailHelper::stringToArray('John Doe <john.doe@presentator.io>, test@presentator.io');

            verify('Addresses count should match', $result)->count(2);
            verify('Address email should exist as a result key', $result)->hasKey('john.doe@presentator.io');
            verify('Address email should exist as a result key', $result)->hasKey('test@presentator.io');
            verify('Address name should match', $result['john.doe@presentator.io'])->equals('John Doe');
            verify('Address name should not be set', $result['test@presentator.io'])->null();
        });
    }

    /**
     * `EmailHelper::arrayToString()` method test.
     */
    public function testArrayToString()
    {
        $this->specify('Parse an empty addresses list array', function() {
            $result = EmailHelper::arrayToString([]);

            verify('Should result in an empty string', $result)->equals('');
        });

        $this->specify('Convert single email address with name array into a string', function() {
            $result = EmailHelper::arrayToString(['john.doe@presentator.io' => 'John Doe']);

            verify('Result should match', $result)->equals('John Doe <john.doe@presentator.io>');
        });

        $this->specify('Convert single email address without name array into a string', function() {
            $result = EmailHelper::arrayToString(['test@presentator.io' => null]);

            verify('Result should match', $result)->equals('test@presentator.io');
        });

        $this->specify('Convert 1+ email addresses list array into a string', function() {
            $result = EmailHelper::arrayToString([
                'john.doe@presentator.io' => 'John Doe',
                'test@presentator.io'     => '',
            ]);

            verify('Result should match', $result)->equals('John Doe <john.doe@presentator.io>, test@presentator.io');
        });
    }
}
