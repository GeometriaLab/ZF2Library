<?php

namespace GeometriaLab\Code\Reflection\DockBlock;

use Zend\Code\Reflection\DocBlock\ParamTag;

class PropertyTag extends ParamTag
{
    /**
     * @var boolean
     */
    protected $isArray = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'property';
    }

    public function isArray()
    {
        return $this->isArray;
    }

    public function initialize($tagDocblockLine)
    {
        $parts = preg_split("#[\s]+#", $tagDocblockLine, 3);

        if (count($parts) < 2 || 0 !== strpos($parts[1], '$')) {
            throw new \Exception('Invalid property definition');
        }

        // Get type
        if (substr($parts[0], -2) === '[]') {
            $this->isArray = true;
            $this->type = substr($parts[0], 0, strlen($parts[1]) - 2);
        } else {
            $this->type = $parts[0];
        }

        $this->variableName = $parts[1];

        // Set property params
        if (isset($parts[2])) {
            $this->description = preg_replace('#\s+#', ' ', $parts[2]);
        }
    }

    /**
     * Get params
     *
     * @return stdClass
     */
    public function getParams()
    {
        // TODO: Move to Zend\Serializer
        $params = json_decode($this->description);

        if ($params === false || !is_object($params)) {
            return new \stdClass();
        } else {
            return $params;
        }
    }
}
