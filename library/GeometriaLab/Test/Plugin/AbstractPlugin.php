<?php

namespace GeometriaLab\Test\Plugin;

use GeometriaLab\Test\TestCaseInterface;

abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @var TestCaseInterface
     */
    protected $test;

    /**
     * Set test object
     *
     * @param TestCaseInterface $test
     * @return AbstractPlugin
     */
    public function setTest(TestCaseInterface $test)
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test object
     *
     * @return TestCaseInterface
     */
    public function getTest()
    {
        return $this->test;
    }
}
