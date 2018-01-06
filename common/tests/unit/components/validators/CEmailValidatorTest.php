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
        $this->specify('Validate wrongly formatted emails', function () {
            $validator = new CEmailValidator([
                'allowName'     => true,
                'allowMultiple' => true,
            ]);

            verify('Validation should not succeed', $validator->validate(''))->false();
            verify('Validation should not succeed', $validator->validate('test@presentator'))->false();
            verify('Validation should not succeed', $validator->validate('test@presentator.io, invalid'))->false();
            verify('Validation should not succeed', $validator->validate('Lorem Ipsum test@presentator.io'))->false();
            verify('Validation should not succeed', $validator->validate('test@presentator.io, Lorem Ipsum test@presentator.io'))->false();
            verify('Validation should not succeed', $validator->validate('test1@presentator.io, test2@presentator.io, test3@presentator.io,'))->false();
        });

        $this->specify('Validate correctly formatted emails', function () {
            $validator = new CEmailValidator([
                'allowName'     => true,
                'allowMultiple' => true,
            ]);

            verify('Validation should succeed', $validator->validate('test@presentator.io'))->true();
            verify('Validation should succeed', $validator->validate('test1@presentator.io, test2@presentator.io'))->true();
            verify('Validation should succeed', $validator->validate('Lorem Ipsum <test@presentator.io>'))->true();
            verify('Validation should succeed', $validator->validate('test@presentator.io, Lorem Ipsum <test@presentator.io>'))->true();
            verify('Validation should succeed (trim test)', $validator->validate('  test1@presentator.io,    test2@presentator.io,John Doe <test3@presentator.io>'))->true();
        });

        $this->specify('Validate email address domain restrictions.', function () {
            $validator = new CEmailValidator([
                'allowMultiple'  => true,
                'allowedDomains' => ['example.com', 'test.com']
            ]);

            verify('Validation should not succeed', $validator->validate('test@presentator.io'))->false();
            verify('Validation should not succeed', $validator->validate('test@example.io'))->false();
            verify('Validation should not succeed', $validator->validate('test1@example.com, test2@presentator.io'))->false();
            verify('Validation should succeed', $validator->validate('test@test.com'))->true();
            verify('Validation should succeed', $validator->validate('test@example.com'))->true();
            verify('Validation should succeed', $validator->validate('test1@example.com, test2@test.com'))->true();
        });
    }
}
