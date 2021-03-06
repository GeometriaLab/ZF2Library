<?php

namespace GeometriaLab\Validator;

use Zend\Validator\ValidatorChain as ZendValidatorChain,
    Zend\Validator\ValidatorInterface as ZendValidatorInterface;

class ValidatorChain extends ZendValidatorChain
{
    /**
     * Cleanup error messages
     */
    public function cleanupMessages()
    {
        $this->messages = array();
    }

    /**
     * Add validator by index
     *
     * @param int $index
     * @param ZendValidatorInterface $validator
     * @param boolean $breakChainOnFailure
     * @return ValidatorChain
     * @throws \InvalidArgumentException
     */
    public function addValidatorByIndex($index, ZendValidatorInterface $validator, $breakChainOnFailure = false)
    {
        if ($index < 0) {
            throw new \InvalidArgumentException("Index too small");
        }

        if ($index > $this->count()) {
            throw new \InvalidArgumentException("Index too large");
        }

        $validatorData = array(
            'instance'            => $validator,
            'breakChainOnFailure' => (boolean)$breakChainOnFailure,
        );

        $this->validators = array_merge(
            array_slice($this->validators, 0, $index),
            array($validatorData),
            array_slice($this->validators, $index)
        );

        return $this;
    }

    /**
     * Remove validator by index
     *
     * @param $index
     * @return array
     * @throws \InvalidArgumentException
     */
    public function removeValidatorByIndex($index)
    {
        if (!isset($this->validators[$index])) {
            throw new \InvalidArgumentException("Invalid index '$index'");
        }

        $validator = $this->validators[$index];

        unset($this->validators[$index]);

        $this->validators = array_values($this->validators);

        return $validator;
    }
}
