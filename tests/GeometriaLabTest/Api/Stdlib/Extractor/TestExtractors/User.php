<?php

namespace GeometriaLabTest\Api\Stdlib\Extractor\TestExtractors;

use GeometriaLab\Api\Stdlib\Extractor\Schema,
    GeometriaLab\Api\Stdlib\Extractor\Extractor;


class User extends Extractor
{
    /**
     * @return Schema
     */
    public function createSchema()
    {
        return new Schema(array(
            'id' => array(
                'source' => 'id',
            ),
            'name' => array(
                'source' => 'name',
                'filters' => array(
                    function($value) {
                        return $value . ' Rodriguez';
                    }
                )
            ),
            'about' => array(
                'source' => 'about',
                'filters' => array(
                    array(
                        'name' => 'StringTrim',
                        'options' => array(
                            'charlist' => 'Futurama',
                        ),
                    ),
                )
            ),
            'order' => array(
                'source' => 'order',
            ),
            'callable' => array(
                'source' => function($object) {
                    return 'Foo';
                },
            ),
        ));
    }
}