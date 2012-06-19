<?php

namespace GeometriaLab\Model\Persistent;

use GeometriaLab\Code\Reflection\DocBlock\Tag\MethodTag,
    GeometriaLab\Model\Schema\Property\PropertyInterface;

class Schema extends \GeometriaLab\Model\Schema
{
    /**
     * Mapper class
     *
     * @var string
     */
    protected $mapperClass;

    /**
     * Mapper params
     *
     * @var array
     */
    protected $mapperOptions = array();

    /**
     * Properties class map
     *
     * @var array
     */
    static protected $propertiesClassMap = array(
        'string'  => 'GeometriaLab\Model\Persistent\Schema\Property\StringProperty',
        'array'   => 'GeometriaLab\Model\Persistent\Schema\Property\ArrayProperty',
        'boolean' => 'GeometriaLab\Model\Persistent\Schema\Property\BooleanProperty',
        'float'   => 'GeometriaLab\Model\Persistent\Schema\Property\FloatProperty',
        'integer' => 'GeometriaLab\Model\Persistent\Schema\Property\IntegerProperty',
    );

    /**
     * Protected constructor
     *
     * @param string $className
     */
    public function __construct($className = null)
    {
        if (!static::getTagManager()->hasTag('method')) {
            static::getTagManager()->addTagPrototype(new MethodTag());
        }

        parent::__construct($className);
    }

    /**
     * Set mapper class
     *
     * @param string $mapperClass
     */
    public function setMapperClass($mapperClass)
    {
        $this->mapperClass = $mapperClass;
    }

    /**
     * Get mapper class
     *
     * @return string
     */
    public function getMapperClass()
    {
        return $this->mapperClass;
    }

    /**
     * Set mapper params
     *
     * @param array $mapperOptions
     */
    public function setMapperOptions($mapperOptions)
    {
        $this->mapperOptions = $mapperOptions;
    }

    /**
     * Get mapper
     *
     * @return array
     */
    public function getMapperOptions()
    {
        return $this->mapperOptions;
    }

    /**
     * Parse class docblock
     *
     * @param string $className
     * @throws \InvalidArgumentException
     */
    protected function parseDocblock($className)
    {
        parent::parseDocblock($className);

        if ($this->mapperClass === null) {
            throw new \InvalidArgumentException('Mapper method tag not present in docblock!');
        }

        foreach($this->getProperties() as $property) {
            if ($property->isPrimary()) {
                return;
            }
        }

        throw new \InvalidArgumentException('Primary property (primary key) not present!');
    }

    /**
     * Parse method tag
     *
     * @param MethodTag $tag
     * @throws \InvalidArgumentException
     */
    protected function parseMethodTag(MethodTag $tag)
    {
        if ($tag->getMethodName() === 'getMapper()') {
            if (!$tag->isStatic() || !class_exists($tag->getReturnType()) || $tag->getParams() === array()) {
                throw new \InvalidArgumentException('Invalid mapper method tag in docblock!');
            }

            $this->setMapperClass($tag->getReturnType());
            $this->setMapperOptions($tag->getParams());
        }
    }


}