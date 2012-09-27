<?php

namespace GeometriaLab\Api\Mvc\Controller\Action\Params;

use Zend\EventManager\ListenerAggregateInterface as ZendListenerAggregateInterface,
    Zend\EventManager\EventManagerInterface as ZendEventManagerInterface,
    Zend\Mvc\MvcEvent as ZendMvcEvent,
    Zend\Stdlib\RequestInterface as ZendRequestInterface;

use GeometriaLab\Api\Exception\InvalidParamsException;

class Listener implements ZendListenerAggregateInterface
{
    /**
     * Listeners we've registered
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * Attach listeners
     *
     * @param ZendEventManagerInterface $events
     * @return void
     */
    public function attach(ZendEventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', ZendMvcEvent::EVENT_DISPATCH, array($this, 'createParams'), 100);
    }

    /**
     * Detach listeners
     *
     * @param ZendEventManagerInterface $events
     * @return void
     */
    public function detach(ZendEventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * @param ZendMvcEvent $e
     * @throws InvalidParamsException
     */
    public function createParams(ZendMvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $request = $e->getRequest();
        $queryParams = $this->getParamsFromRequest($request);

        // @TODO Stub
        $id = $routeMatch->getParam('id');
        if ($id !== null) {
            $queryParams['id'] = $id;
        }

        /* @var Params $params */
        $params = $e->getApplication()->getServiceManager()->get('Params');
        $params->populate($queryParams);

        $routeMatch->setParam('params', $params);

        if (!$params->isValid()) {
            $exception = new InvalidParamsException();
            $exception->setParams($params);

            $e->setParam('exception', $exception);
            $e->setError($exception->getMessage());

            $e->getApplication()->getEventManager()->trigger(ZendMvcEvent::EVENT_DISPATCH_ERROR, $e);
            $e->stopPropagation();

            return;
        }
    }

    /**
     * @static
     * @param ZendRequestInterface $request
     * @return mixed
     */
    protected function getParamsFromRequest(ZendRequestInterface $request)
    {
        $queryParams = $request->getQuery()->toArray();

        unset($queryParams['q']);

        foreach ($queryParams as $key => $value) {
            if ($key[0] == '_') {
                unset($queryParams[$key]);
            }
        }

        return $queryParams;
    }
}
