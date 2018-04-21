<?php
namespace common\tests\unit\components\validators;

use common\components\validators\HotspotsValidator;

/**
 * HotspotsValidator tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotsValidatorTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `HotspotsValidator::validate()` method test.
     */
    public function testValidation()
    {
        $this->specify('Validate wrongly formatted hotspots', function () {
            $validator = new HotspotsValidator();

            $tests = [
                ['test' => 'test'],
                ['test' => null],
                ['test' => []],
                ['test' => ['height' => 1, 'top' => 1, 'left' => 1, 'link' => 1]],
                ['test' => ['width' => 1, 'top' => 1, 'left' => 1, 'link' => 1]],
                ['test' => ['width' => 1, 'height' => 1, 'left' => 1, 'link' => 1]],
                ['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'link' => 1]],
                ['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1]],
                ['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1, 'invalid' => 1]],
                ['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1, 'transition' => 'invalid']],
                ['test1' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1], 'test2' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1, 'transition' => 'invalid']],
            ];

            foreach ($tests as $data) {
                verify($validator->validate($data))->false();
                verify($validator->validate(json_encode($data)))->false();
            }
        });

        $this->specify('Validate correctly formatted hotspots', function () {
            $validator = new HotspotsValidator();

            $tests = [
                [],
                null,
                ['test' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1]],
                ['test' => ['width' => 1.1, 'height' => 1.2, 'top' => 1.3, 'left' => 1.4, 'link' => 'test']],
                ['test' => ['width' => '1', 'height' => '1', 'top' => '1', 'left' => '1', 'link' => '1', 'transition' => 'fade']],
                ['test1' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 1, 'transition' => 'fade'], 'test2' => ['width' => 1, 'height' => 1, 'top' => 1, 'left' => 1, 'link' => 'test']],
            ];

            foreach ($tests as $data) {
                verify($validator->validate($data))->true();
                verify($validator->validate(json_encode($data)))->true();
            }
        });
    }
}
