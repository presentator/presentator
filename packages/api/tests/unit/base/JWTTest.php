<?php
namespace presentator\api\tests\unit\base;

use presentator\api\base\JWT;

/**
 * JWT wrapper component tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class JWTTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `JWT::unsafeDecode()` method test.
     */
    public function testUnsafeDecode()
    {
        $this->specify('Try to decode invalid formatted token', function() {
            $this->tester->expectThrowable(\Exception::class, function() {
                JWT::unsafeDecode('invalid.test');
            });
        });

        $this->specify('Try to decode valid formatted token', function() {
            $payload = JWT::unsafeDecode(JWT::encode(['name' => 'test'], '123456'));

            verify('payload should contains the name claim', $payload->name)->equals('test');
        });
    }

    /**
     * `JWT::isValid()` method test.
     */
    public function testIsValid()
    {
        $this->specify('Try with invalid secret', function() {
            $token = JWT::encode(['name' => 'test', 'exp' => time() + 100], '123456');

            verify('should return false', JWT::isValid($token, '123'))->false();
        });

        $this->specify('Try with valid secret but expired expiration', function() {
            $token = JWT::encode(['name' => 'test', 'exp' => time() - 100], '123456');

            verify('should return false', JWT::isValid($token, '123456'))->false();
        });

        $this->specify('Try with valid secret but expired expiration', function() {
            $token = JWT::encode(['name' => 'test', 'exp' => time() + 100], '123456');

            verify('should return true', JWT::isValid($token, '123456'))->true();
        });
    }
}
