<?php
namespace common\tests\unit\components\validators;

use common\components\validators\CEmailValidator;

/**
 * CEmailValidator tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CEmailValidatorTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `CEmailValidator::stringToArray()` method test.
     */
    public function testValidation()
    {
        $validator = new CEmailValidator();

        $this->specify('Validate wrongly formatted emails', function () use ($validator) {
            verify('Validation should not succeed', $validator->validate(''))->false();
            verify('Validation should not succeed', $validator->validate('test@presentator'))->false();
            verify('Validation should not succeed', $validator->validate('test@presentator.io, invalid'))->false();
            verify('Validation should not succeed', $validator->validate('Lorem Ipsum test@presentator.io'))->false();
            verify('Validation should not succeed', $validator->validate('test@presentator.io, Lorem Ipsum test@presentator.io'))->false();
            verify('Validation should not succeed', $validator->validate('test1@presentator.io, test2@presentator.io, test3@presentator.io,'))->false();
        });

        $this->specify('Validate correctly formatted emails', function () use ($validator) {
            verify('Validation should succeed', $validator->validate('test@presentator.io'))->true();
            verify('Validation should succeed', $validator->validate('test1@presentator.io, test2@presentator.io'))->true();
            verify('Validation should succeed', $validator->validate('Lorem Ipsum <test@presentator.io>'))->true();
            verify('Validation should succeed', $validator->validate('test@presentator.io, Lorem Ipsum <test@presentator.io>'))->true();
            verify('Validation should succeed (trim test)', $validator->validate('  test1@presentator.io,    test2@presentator.io,John Doe <test3@presentator.io>'))->true();
        });
    }
}
