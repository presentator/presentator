<?php
namespace common\tests\unit\components\swiftmailer;

use common\components\swiftmailer\CMessage;

/**
 * CMessage tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CMessageTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * Helper method to get protected/private property via Reflection.
     * @param  mixed  $obj
     * @param  string $prop
     * @return mixed
     */
    protected function getPropValue($obj, $prop)
    {
        $reflectionObject   = new \ReflectionObject($obj);
        $reflectionProperty = $reflectionObject->getProperty($prop);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($obj);
    }

    /**
     * Helper method to set protected/private property via Reflection.
     * @param  mixed  $obj
     * @param  string $prop
     * @param  mixed  $val
     */
    protected function setPropValue($obj, $prop, $val)
    {
        $reflectionObject   = new \ReflectionObject($obj);
        $reflectionProperty = $reflectionObject->getProperty($prop);
        $reflectionProperty->setAccessible(true);

        $reflectionProperty->setValue($obj, $val);
    }

    /**
     * `CMessage::useMailQueue()` method test.
     */
    public function testUseMailQueue()
    {
        $message = new CMessage();

        $message->useMailQueue(true);
        verify('useMailQueue property value should match', $this->getPropValue($message, 'useMailQueue'))->true();

        $message->useMailQueue(false);
        verify('useMailQueue property value should match', $this->getPropValue($message, 'useMailQueue'))->false();

        $message->useMailQueue(1);
        verify('useMailQueue property value should match', $this->getPropValue($message, 'useMailQueue'))->true();

        $message->useMailQueue(0);
        verify('useMailQueue property value should match', $this->getPropValue($message, 'useMailQueue'))->false();

        $message->useMailQueue();
        verify('useMailQueue default property value should match', $this->getPropValue($message, 'useMailQueue'))->true();
    }

    /**
     * `CMessage::isUsingMailQueue()` method test.
     */
    public function testIsUsingMailQueue()
    {
        $message = new CMessage();

        $this->setPropValue($message, 'useMailQueue', false);
        verify('Return value should match', $message->isUsingMailQueue())->false();

        $this->setPropValue($message, 'useMailQueue', true);
        verify('Return value should match', $message->isUsingMailQueue())->true();
    }
}
