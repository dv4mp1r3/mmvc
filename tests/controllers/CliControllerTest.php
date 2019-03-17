<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use mmvc\controllers\BaseController;
use mmvc\controllers\CliController;

class CliControllerTest extends TestCase
{
    /**
     * @var CliController $cliController
     */
    protected $cliController;

    public function setUp() : void
    {
        $this->cliController = new CliController();
    }

    protected function tearDown() : void
    {
        unset($this->cliController);
    }

    public function testGetInput() : void
    {
        $name = 'testVal';
        $actialValue = 1;
        $_SERVER['argv'][$name] = $actialValue;
        $returnedValue = self::callMethod(
            $this->cliController,
            'getInput',
            [
                $name,
                FILTER_VALIDATE_INT,
                null,
            ]
        );
        $this->assertEquals($returnedValue, $actialValue);
    }

    /**
     * @param object $obj
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws ReflectionException
     */
    public static function callMethod($obj, $name, $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}