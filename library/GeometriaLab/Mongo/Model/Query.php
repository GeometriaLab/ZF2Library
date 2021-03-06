<?php

namespace GeometriaLab\Mongo\Model;

use GeometriaLab\Model\Persistent\Mapper\Query as AbstractQuery,
    GeometriaLab\Model\Persistent\Mapper\QueryInterface,
    GeometriaLab\Model\Schema\Schema as ModelSchema,
    GeometriaLab\Model\Schema\Manager as SchemaManager,
    GeometriaLab\Model\Schema\Property\ArrayProperty,
    GeometriaLab\Model\Schema\Property\ModelProperty,
    GeometriaLab\Model\Schema\Property\Validator\Exception\InvalidValueException;

class Query extends AbstractQuery
{
    const OPERATOR_ACCEPTS_VALUE = 1;
    const OPERATOR_ACCEPTS_ARRAY = 2;

    protected $operators = array(
        '$gt'     => self::OPERATOR_ACCEPTS_VALUE,
        '$gte'    => self::OPERATOR_ACCEPTS_VALUE,
        '$lt'     => self::OPERATOR_ACCEPTS_VALUE,
        '$lte'    => self::OPERATOR_ACCEPTS_VALUE,
        '$all'    => self::OPERATOR_ACCEPTS_ARRAY,
        '$mod'    => self::OPERATOR_ACCEPTS_ARRAY,
        '$ne'     => self::OPERATOR_ACCEPTS_VALUE,
        '$in'     => self::OPERATOR_ACCEPTS_ARRAY,
        '$nin'    => self::OPERATOR_ACCEPTS_ARRAY,
    );

    protected $notImplementedOperators = array('$or' => 1, '$nor' => 1, '$and' => 1);

    /**
     * Set selected fields
     *
     * @param array $fields
     * @return QueryInterface|Query|AbstractQuery
     * @throws \InvalidArgumentException
     */
    public function select(array $fields)
    {
        foreach($fields as $field => $include) {
            if (!$this->getModelSchema()->hasProperty($field)) {
                throw new \InvalidArgumentException("Selected field '$field' not present in model!");
            }
        }

        $this->select = $fields;

        return $this;
    }

    /**
     * Add where condition
     *
     * @param array $where
     * @return AbstractQuery|QueryInterface|Query
     * @throws \InvalidArgumentException
     */
    public function where(array $where)
    {
        if (!empty($where)) {
            $conditions = array();
            foreach($where as $field => $value) {
                if (isset($this->notImplementedOperators[$field])) {
                    throw new \InvalidArgumentException("Operator $field not implemented yet");
                }

                if (is_array($value)) {
                    foreach ($value as $operator => $operatorValue) {
                        if (isset($this->operators[$operator])) {
                            if ($this->operators[$operator] === self::OPERATOR_ACCEPTS_ARRAY) {
                                if (!is_array($operatorValue)) {
                                    throw new \InvalidArgumentException("Value of operator $operator must be array");
                                }
                                foreach($operatorValue as &$item) {
                                    $item = $this->prepareFieldValue($field, $item);
                                }
                            } else if ($this->operators[$operator] === self::OPERATOR_ACCEPTS_VALUE) {
                                $operatorValue = $this->prepareFieldValue($field, $operatorValue);
                            }
                        }

                        $conditions[$field][$operator] = $operatorValue;
                    }
                } else {
                    $conditions[$field] = $this->prepareFieldValue($field, $value);
                }
            }

            if ($this->where === null) {
                $this->where = $conditions;
            } else {
                 $this->where = array_merge($this->where, $conditions);
            }
        }

        return $this;
    }

    /**
     * Prepare field value
     *
     * @param string $field
     * @param mixed $value
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function prepareFieldValue($field, $value)
    {
        return $this->prepareModelFieldValue($this->getModelSchema(), $field, $field, $value);
    }

    /**
     * Prepare field value for specified model
     *
     * @todo Refactor and optimize!
     *
     * @param ModelSchema $modelSchema
     * @param $fullField
     * @param $field
     * @param $value
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function prepareModelFieldValue(ModelSchema $modelSchema, $fullField, $field, $value)
    {
        $hasDotNotation = strpos($field, '.') !== false;
        $subFields = '';

        if ($hasDotNotation) {
            list($field, $subFields) = explode('.', $field, 2);
        }

        if (!$modelSchema->hasProperty($field)) {
            throw new \InvalidArgumentException("Field in where '$fullField' not present in model!");
        }
        /* @var ModelProperty|ArrayProperty $property */
        $property = $modelSchema->getProperty($field);

        if ($hasDotNotation) {
            if ($property instanceof ArrayProperty) {
                /* @var ModelProperty $subProperty */
                $subProperty = $property->getItemProperty();

                if ($subProperty === null) {
                    return $value;
                }

                if (strpos($subFields, '.') !== false) {
                    list($subField, $subSubFields) = explode('.', $subFields, 2);
                    if (is_numeric($subField)) {
                        $subFields = $subSubFields;
                    }
                }

                if ($subProperty instanceof ModelProperty) {
                    $className = $subProperty->getModelClass();
                    $schema = $className::getSchema();

                    return $this->prepareModelFieldValue($schema, $fullField, $subFields, $value);
                } else {
                    throw new \InvalidArgumentException("Invalid field '$fullField' not present in model!");
                }
            } elseif ($property instanceof ModelProperty) {
                $className = $property->getModelClass();
                $schema = $className::getSchema();

                return $this->prepareModelFieldValue($schema, $fullField, $subFields, $value);
            } else {
                throw new \InvalidArgumentException("Invalid field '$fullField' not present in model!");
            }
        } else if ($property instanceof ArrayProperty) {
            if ($property->getItemProperty() === null) {
                return $value;
            }
            $property = $property->getItemProperty();
        }

        // @todo Validate or not?
        /*
        try {
            $value = $property->getFilterChain()->filter($value);
            $value = $property->validate($value);
        } catch (InvalidValueException $e) {
            throw new \InvalidArgumentException("Invalid value for field '$field': " . $e->getMessage());
        }
        */

        return $value;
    }
}
