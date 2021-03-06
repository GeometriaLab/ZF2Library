<?php

namespace GeometriaLabTest\Model\Persistent;

use GeometriaLabTest\Model\Persistent\TestModels\Model,
    GeometriaLabTest\Model\Persistent\TestModels\ModelWithInvalidDefinition,
    GeometriaLabTest\Model\Persistent\TestModels\ModelWithInvalidDefinition2,
    GeometriaLabTest\Model\Persistent\TestModels\ModelWithoutDefinition,
    GeometriaLabTest\Model\Persistent\TestModels\WithInvalidRelations\NotExists,
    GeometriaLabTest\Model\Persistent\TestModels\WithInvalidRelations\Inherit,
    GeometriaLabTest\Model\Persistent\TestModels\WithInvalidRelations\NotRelation,
    GeometriaLabTest\Model\TestModels\SubModel;


class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInheritRelation()
    {
        $model = new TestModels\WithInvalidRelations\Inherit();
        $this->assertTrue($model->has('foo'));
    }

    public function testGetRelationNotExists()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Property \'foo\' not present in model');
        $model = new TestModels\WithInvalidRelations\NotExists();
        $model->getRelation('foo');
    }

    public function testGetRelationNotRelation()
    {
        $this->setExpectedException('\InvalidArgumentException', '\'foo\' is not relation');
        $model = new TestModels\WithInvalidRelations\NotRelation();
        $model->getRelation('foo');
    }

    public function testSetNotExists()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Property \'bar\' does not exists');
        $model = new TestModels\Model();
        $model->set('bar', 'baz');
    }

    public function testSetInvalidProperty()
    {
        $this->setExpectedException('GeometriaLab\Model\Schema\Property\Validator\Exception\InvalidValueException');
        $model = new TestModels\Model();
        $model->set('id', 'foo');
    }

    public function testSetBySetter()
    {
        $model = new TestModels\Model();
        $model->set('foo', 'bar');
        $this->assertEquals('bar', $model->foo);
    }

    public function testSetBelongsToRelation()
    {
        $man = new \GeometriaLabTest\Model\Persistent\Relation\TestModels\Man(array('name' => 'foo'));
        $man->id = 2;
        $man->save();
        $dog = new \GeometriaLabTest\Model\Persistent\Relation\TestModels\Dog();
        $dog->set('manId', 2);
        $this->assertEquals('foo', $dog->man->name);
    }

    public function testSave()
    {
        $model = new Model();

        $model->populate($this->getData());

        // create if new
        $this->assertNull($model->id);
        $this->assertTrue($model->save());
        $this->assertNotNull($model->id);

        $newModel = $model::getMapper()->get($model->id);

        $this->assertEquals($newModel, $model);

        // update if changed
        $model->set('integerProperty', 11);

        $this->assertTrue($model->save());

        $newModel = $model::getMapper()->get($model->id);

        $this->assertEquals($newModel, $model);

        // no changes - nothing to do
        $this->assertFalse($model->save());
    }

    public function testDelete()
    {
        $model = new Model();

        $model->populate($this->getData())
              ->save();

        $this->assertTrue($model->delete());

        $this->assertTrue($model->isNew());

        $this->assertNull($model::getMapper()->get($model->id));
    }

    public function testDeleteNotSaved()
    {
        $model = new Model();

        $model->populate($this->getData());
        $this->assertFalse($model->delete());
    }

    public function testIsNew()
    {
        $model = new Model();

        $model->populate($this->getData());
        $this->assertTrue($model->isNew());

        $model->save();

        $this->assertFalse($model->isNew());
    }

    public function testIsChanged()
    {
        $model = new Model();

        $model->populate($this->getData());
        $this->assertTrue($model->isChanged());

        $model->save();

        $this->assertFalse($model->isChanged());

        $model->set('integerProperty', 11);

        $this->assertTrue($model->isChanged());
    }

    public function testIsPropertyChanged()
    {
        $model = new Model();

        $model->populate($this->getData())
              ->save();

        $this->assertFalse($model->isPropertyChanged('integerProperty'));

        $model->set('integerProperty', 11);

        $this->assertTrue($model->isPropertyChanged('integerProperty'));
    }

    public function testGetChangedProperties()
    {
        $model = new Model();

        $model->populate($this->getData())
              ->save();

        $model->set('floatProperty', 11.0);
        $model->set('integerProperty', 11);


        $this->assertEquals(array('floatProperty', 'integerProperty'), $model->getChangedProperties());
    }

    public function testGetClean()
    {
        $model = new Model();

        $model->populate($this->getData())
              ->save();

        $model->set('integerProperty', 11);

        $this->assertEquals(10, $model->getClean('integerProperty'));
    }

    public function testGetCleanNotExists()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Property \'bar\' does not exists');
        $model = new Model();
        $model->populate($this->getData())
              ->save();
        $model->getClean('bar');
    }

    public function testGetMapper()
    {
        $mapper = Model::getMapper();
        $this->assertInstanceOf('\GeometriaLab\Model\Persistent\Mapper\Mock', $mapper);
    }

    public function testGetMapperWithoutDefinition()
    {
        $this->setExpectedException('\InvalidArgumentException');
        ModelWithoutDefinition::getMapper();
    }

    public function testGetMapperWithInvalidDefinition()
    {
        $this->setExpectedException('\InvalidArgumentException');
        ModelWithInvalidDefinition::getMapper();
    }

    public function testGetMapperWithInvalidDefinition2()
    {
        $this->setExpectedException('\InvalidArgumentException');
        ModelWithInvalidDefinition2::getMapper();
    }

    protected function getData()
    {
        return array(
            'floatProperty'   => 3.4,
            'integerProperty' => 10,
            'stringProperty'  => 'test',
            'subTest'         => new SubModel(array('id' => 1, 'title' => 'Hello')),
            'arrayOfInteger'  => array(9, 10, 11, 12, 13),
            'arrayOfString'   => array('string1', 'string2'),
            'arrayOfSubTest'  => array(new SubModel(array('id' => 1, 'title' => 'Hello')), new SubModel(array('id' => 2, 'title' => 'Hello2')))
        );
    }
}