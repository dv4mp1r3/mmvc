<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use mmvc\models\BaseModel;

class BaseModelTest extends TestCase {

    /**
     * @var BaseModel $model
     */
    protected $model;
    
    public function setUp() : void
    {
        $this->model = new BaseModel();
    }
    
    protected function tearDown() : void
    {
        $this->model = null;
    }
    
    public function testName() : void
    {
        $this->assertEquals('BaseModel', $this->model->getName());
    }
    
    public function testClassName() : void
    {
        $this->assertEquals('mmvc\\models\\BaseModel', $this->model->getClassName());
    }
}
