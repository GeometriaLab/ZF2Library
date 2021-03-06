<?php
/**
 * Created by JetBrains PhpStorm.
 * User: max
 * Date: 27.07.12
 * Time: 15:28
 * To change this template use File | Settings | File Templates.
 */
namespace GeometriaLab\Api\Mvc\Controller;

use Zend\Mvc\Controller\AbstractController as ZendAbstractController;
use Zend\Http\Request as ZendHttpRequest;
use Zend\Mvc\Exception as ZendMvcException;
use Zend\Mvc\MvcEvent as ZendMvcEvent;
use Zend\Stdlib\RequestInterface as ZendRequest;
use Zend\Stdlib\ResponseInterface as ZendResponse;

/**
 * Abstract Api Rest controller
 */
abstract class AbstractController extends ZendAbstractController
{
    /**
     * @var string
     */
    protected $eventIdentifier = __CLASS__;

    /**
     * Dispatch a request
     *
     * If the route match includes an "action" key, then this acts basically like
     * a standard action controller. Otherwise, it introspects the HTTP method
     * to determine how to handle the request, and which method to delegate to.
     *
     * @events dispatch.pre, dispatch.post
     * @param  ZendRequest $request
     * @param  null|ZendResponse $response
     * @return mixed|ZendResponse
     * @throws ZendMvcException\InvalidArgumentException
     */
    public function dispatch(ZendRequest $request, ZendResponse $response = null)
    {
        if (!$request instanceof ZendHttpRequest) {
            throw new ZendMvcException\InvalidArgumentException('Expected an HTTP request');
        }

        return parent::dispatch($request, $response);
    }

    /**
     * Handle the request
     *
     * @param  ZendMvcEvent $e
     * @return mixed
     * @throws ZendMvcException\DomainException if no route matches in event or invalid HTTP method
     */
    public function onDispatch(ZendMvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();

        $action = $routeMatch->getParam('action');
        $params = $routeMatch->getParam('params');

        $return = $this->$action($params);

        // Emit post-dispatch signal, passing:
        // - return from method, request, response
        // If a listener returns a response object, return it immediately
        $e->setResult($return);
        return $return;
    }
}
