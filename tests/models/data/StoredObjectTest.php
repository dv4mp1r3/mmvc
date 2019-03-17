<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use mmvc\models\data\StoredObject;

class StoredObjectTest extends TestCase {

    /**
     * @var StoredObject $model
     */
    protected $model;
    
    public function setUp() : void
    {
        $this->model = new StoredObject('test');
        $this->model->property = 'value';
    }
    
    protected function tearDown() : void
    {
        $this->model = null;
    }
    
    public function testGettingProperty() : void
    {      
        $result = $this->model->property;
        
        $this->assertEquals('value', $result);
    }
    
    public function testAsArray() : void
    {        
        $result = $this->model->asArray();
        
        $this->assertEquals(['property' => 'value'], $result);
    }
    
    public function testAsJson() : void
    {        
        $result = $this->model->asJson();
        
        $this->assertEquals(json_encode(['property' => 'value']), $result);
    }

    public function testUndefinedPropertyAccess() : void
    {
        $this->expectException('\\Exception');
        $result = $this->model->undefinedProperty;
    }
    
    public function testIsNew() : void
    {
        $this->model->someProperty = 'someValue';
        $this->model->save();
        $this->assertEquals(false, $this->model->isNew());
    }
}
