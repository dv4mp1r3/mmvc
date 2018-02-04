<?php

require_once __DIR__.'/../../../src/models/data/StoredObject.php';

use PHPUnit\Framework\TestCase;
use mmvc\models\data\StoredObject;

class StoredObjectTest extends TestCase { 
    
    protected $model;
    
    public function setUp()
    {
        $this->model = new StoredObject('test');
        $this->model->property = 'value';
    }
    
    protected function tearDown()
    {
        $this->model = null;
    }
    
    public function testGettingProperty()
    {      
        $result = $this->model->property;
        
        $this->assertEquals('value', $result);
    }
    
    public function testAsArray()
    {        
        $result = $this->model->asArray();
        
        $this->assertEquals(['property' => 'value'], $result);
    }
    
    public function testAsJson()
    {        
        $result = $this->model->asJson();
        
        $this->assertEquals(json_encode(['property' => 'value']), $result);
    }
}
