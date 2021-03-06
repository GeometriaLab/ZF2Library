<?php

namespace GeometriaLab\Permissions\Assertion;

use Zend\Stdlib\Glob as ZendGlob,
    Zend\ServiceManager\FactoryInterface as ZendFactoryInterface,
    Zend\ServiceManager\ServiceLocatorInterface as ZendServiceLocatorInterface;

class Service implements ZendFactoryInterface
{
    /**
     * @var Assertion
     */
    private $assertion;
    /**
     * @var array
     */
    private $config = array();
    /**
     * @var ZendServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Create service
     *
     * @param ZendServiceLocatorInterface $serviceLocator
     * @return Assertion
     */
    public function createService(ZendServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);

        $config = $serviceLocator->get('Configuration');
        if (isset($config['assertion'])) {
            $this->setConfig($config['assertion']);
        }

        $this->addResources();

        return $this->getAssertion();
    }

    /**
     * Set Service Locator
     *
     * @param ZendServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ZendServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get Service Locator
     *
     * @return ZendServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set config
     *
     * @param array $config
     * @return Service
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get Assertion object
     *
     * @return Assertion
     */
    public function getAssertion()
    {
        if ($this->assertion === null) {
            $this->assertion = new Assertion();
        }
        return $this->assertion;
    }

    /**
     * Add all resources
     *
     * @return Service
     */
    private function addResources()
    {
        $namespace = $this->getNamespace();
        $pathPattern = $this->getResourcesPath() . '*';

        foreach (ZendGlob::glob($pathPattern, ZendGlob::GLOB_BRACE) as $file) {
            /* @var \GeometriaLab\Permissions\Assertion\Resource\ResourceInterface $resource */
            $resourceName = ucfirst(pathinfo($file, PATHINFO_FILENAME));
            $resourceClassName = $namespace . '\\' . $resourceName;

            $this->getAssertion()->addResource(
                new $resourceClassName($resourceName)
            );
        }

        return $this;
    }

    /**
     * Get Permissions' namespace
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getNamespace()
    {
        if (empty($this->config['__NAMESPACE__'])) {
            throw new \InvalidArgumentException('Need not empty "assertion.__NAMESPACE__" param in config');
        }
        return $this->config['__NAMESPACE__'];
    }

    /**
     * Get path to the resources
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getResourcesPath()
    {
        if (empty($this->config['base_dir'])) {
            throw new \InvalidArgumentException('Need not empty "assertion.base_dir" param in config');
        }
        return rtrim($this->config['base_dir'], '/') . '/';
    }
}
