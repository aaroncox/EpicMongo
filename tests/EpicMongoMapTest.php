<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoMapTest extends PHPUnit_Framework_TestCase
{
	/**
	 * testEpicMongoMapExists
	 *
	 * @return void
	 * @author Aaron Cox
	 **/
	public function testExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Map'));
	}

	public function testInstanceOf() {
		$map = new Epic_Mongo_Map;
		$this->assertInstanceOf('Epic_Mongo_Map', $map);
	}
	
	public function testGetType() {
		$map = new Epic_Mongo_Map;
		$map->addType('test', 'Test_Mapper_Class');
		$this->assertEquals($map->getClass('test'), 'Test_Mapper_Class');
		$this->assertTrue($map->hasClass('test'));
		$this->assertFalse($map->hasClass('nope'));
	}
	
	/**
   * @expectedException Epic_Mongo_Exception
   */
	public function testAddTypeException() { 
		$map = new Epic_Mongo_Map;
		$map->addType('test', 'Is_Not_A_Class');
	}
	
	public function testAddTypeArray() { 
		$map = new Epic_Mongo_Map;
		$map->addType(array(
			'test1' => 'Test_Mapper_Class',
			'test2' => 'Test_Mapper_Class',
		));
		$this->assertEquals($map->getClass('test1'), 'Test_Mapper_Class');
		$this->assertEquals($map->getClass('test2'), 'Test_Mapper_Class');
	}
	
	/**
   * @expectedException Epic_Mongo_Exception
   */
	public function testGetClassException() {
		$map = new Epic_Mongo_Map;
		$map->getClass('doesnt_exist');
	}
	
	public function testGetInstance() {
		$map = new Epic_Mongo_Map;
		$map->addType('test', 'Test_Mapper_Class');
		$testInstance = $map->getInstance('test');
		$this->assertInstanceOf('Test_Mapper_Class', $testInstance);
		$this->assertFalse($map->getStatic('test') === $testInstance);
		$this->assertFalse($map->getInstance('test') === $testInstance);
	}
		
	public function testGetStatic() {
		$map = new Epic_Mongo_Map;
		$map->addType('test', 'Test_Mapper_Class');
		$testStatic = $map->getStatic('test');
		$this->assertInstanceOf('Test_Mapper_Class', $testStatic);
		$this->assertTrue($map->getStatic('test') === $testStatic);
	}
	
	/**
   * @expectedException Epic_Mongo_Exception
   */
	public function testAddTypeExtendsException() {
		$map = new Epic_Mongo_map;
		$map->addType('test', 'Test_Mapper_Class');
		$map->addType('test', 'Test_Mapper_Class_NoExtends');
	}
	
	public function testAddTypeExtends() {
		$map = new Epic_Mongo_map;
		$map->addType('test', 'Test_Mapper_Class');
		$map->addType('test', 'Test_Mapper_Class_Extends');
		$this->assertEquals('Test_Mapper_Class_Extends', $map->getClass('test'));
	}
	
} // END class EpicMongoTest extends PHPUnit_Framework_TestCase

class Test_Mapper_Class { }
class Test_Mapper_Class_Extends extends Test_Mapper_Class { }
class Test_Mapper_Class_NoExtends { }