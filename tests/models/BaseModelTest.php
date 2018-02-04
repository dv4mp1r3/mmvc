<?php

require_once __DIR__.'/../../src/models/BaseModel.php';

use PHPUnit\Framework\TestCase;
use mmvc\models\BaseModel;

class BaseModelTest extends TestCase {
    
    protected $model;
    
    public function setUp()
    {
        $this->model = new BaseModel();
    }
    
    protected function tearDown()
    {
        $this->model = null;
    }
    
    public function testName()
    {
        $this->assertEquals('BaseModel', $this->model->getName());
    }
    
    public function testClassName()
    {
        $this->assertEquals('mmvc\\models\\BaseModel', $this->model->getClassName());
    }
}
