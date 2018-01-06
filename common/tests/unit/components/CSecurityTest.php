<?php
namespace common\tests\unit\components;

use Yii;
use yii\base\InvalidParamException;

/**
 * CSecurity tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CSecurityTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `CSecurity::generateRandomString()` method test.
     */
    public function testGenerateRandomString()
    {
        $this->specify('No declared alphabet', function () {
            $str = Yii::$app->security->generateRandomString(6);
            verify('Valid generated string length', strlen($str))->equals(6);
        });

        $this->specify('Declared alphabet and invalid length', function () {
            $str = Yii::$app->security->generateRandomString(6, [
                ['abcd', 5],
                ['1234', 3],
            ]);
        }, ['throws' => new InvalidParamException]);

        $this->specify('Declared alphabet and valid length', function () {
            $alphabet1 = 'abcd';
            $alphabet2 = '1234';
            $alphabetCount1 = 0;
            $alphabetCount2 = 0;

            $str = Yii::$app->security->generateRandomString(6, [
                [$alphabet1, 2], // with specified min occurance
                [$alphabet2],    // without specified min occurance (1 by default)
            ]);

            foreach (str_split($str) as $value) {
                if (strpos($alphabet1, $value) !== false) {
                    $alphabetCount1++;
                } elseif (strpos($alphabet2, $value) !== false) {
                    $alphabetCount2++;
                } else {
                    break;
                }
            }

            verify('Valid generated string length', strlen($str))->equals(6);
            verify('Should have at least 2 occurance from alphabet 1', $alphabetCount1)->greaterOrEquals(2);
            verify('Should have at least 1 occurance from alphabet 2', $alphabetCount2)->greaterOrEquals(1);
        });
    }

    /**
     * `CSecurity::isTimestampTokenValid()` method test.
     */
    public function testIsTimestampTokenValid()
    {
        $hash = Yii::$app->security->generateRandomString(6);

        $this->specify('Falsely validate timestamp token', function () use ($hash) {
            verify('Unformatted token', Yii::$app->security->isTimestampTokenValid($hash . time()))->false();
            verify('Empty token string', Yii::$app->security->isTimestampTokenValid(''))->false();
            verify('Expired token', Yii::$app->security->isTimestampTokenValid($hash . '_' . strtotime('- 2 hours'), 3600))->false();
        });

        $this->specify('Successfully validate timestamp token', function () use ($hash) {

            verify(Yii::$app->security->isTimestampTokenValid($hash . '_' . time()))->true();
            verify(Yii::$app->security->isTimestampTokenValid($hash . '_' . strtotime('- 1 day'), 3600 * 24))->true();
        });
    }
}
