<?php

namespace GeometriaLabTest\Model\Schema;

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testAddProperty()
    {
        $modelSchema = new \GeometriaLab\Model\Schema\Schema();
        $persistentModelProperty = new \GeometriaLab\Model\Schema\Property\IntegerProperty(array('name' => 'integerProperty'));
        $modelSchema->addProperty($persistentModelProperty);

        $this->assertTrue($modelSchema->hasProperty('integerProperty'));
    }

    public function testAddBadProperty()
    {
        $this->setExpectedException('RuntimeException', 'Property \'integerProperty\' must implement \'GeometriaLab\Model\Persistent\Schema\Property\PropertyInterface\' interface, but \'GeometriaLab\Model\Schema\Property\PropertyInterface\' is given');

        $persistentModelSchema = new \GeometriaLab\Model\Persistent\Schema\Schema();
        $modelProperty = new \GeometriaLab\Model\Schema\Property\IntegerProperty(array('name' => 'integerProperty'));
        $persistentModelSchema->addProperty($modelProperty);
    }
}

