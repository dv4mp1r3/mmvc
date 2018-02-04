<?php

require_once __DIR__.'/../../../src/models/data/StoredObject.php';

use mmvc\models\data\StoredObject;

class StoredObjectTest extends PHPUnit_Framework_TestCase { 
    
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
    
}
