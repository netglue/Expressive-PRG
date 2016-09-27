<?php
/**
 * This file is part of the Expressive PRG Package
 * Copyright 2016 Net Glue Ltd (https://netglue.uk).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NetglueExpressive\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Session\Container;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Middleware to implement the PRG Pattern in a Zend Expressive app
 */
class PostRedirectGet
{

    /**
     * Default Session Container Name
     */
    const SESSION_CONTAINER = 'netglue_prg';

    /**
     * Request Attribute Key
     */
    const KEY = 'prg';

    /**
     * Session Container
     *
     * @var Container
     */
    protected $container;

    /**
     * Invoke Middleware
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  callable $next
     * @return Response
     */
    public function __invoke(
        Request $request,
        Response $response,
        callable $next = null
    ) {
        $method    = $request->getMethod();
        $container = $this->getSessionContainer();

        /**
         * If this is a POST request, store the data in the session
         * and return a redirect response with the original URI
         */
        if ($request->getMethod() === 'POST') {
            $container->setExpirationHops(1, 'post');
            $container->post = $request->getParsedBody();

            return new RedirectResponse($request->getUri(), 303);
        }

        /**
         * Modify the request to include an attribute set to either false
         * indicating that no post data is present, or set to the value of
         * the posted data
         */
        if ($request->getMethod() === 'GET') {
            $value = (null !== $container->post)
                   ? $container->post
                   : false;
            unset($container->post);
            $request = $request->withAttribute(static::KEY, $value);
        }

        if ($next) {
            return $next($request, $response);
        }

        return $response;
    }

    /**
     * Return session container
     *
     * @return Container
     */
    public function getSessionContainer() : Container
    {
        if (!$this->container) {
            $this->container = new Container(static::SESSION_CONTAINER);
        }

        return $this->container;
    }

    /**
     * Set session container
     *
     * @param  Container $container
     * @return void
     */
    public function setSessionContainer(Container $container)
    {
        $this->container = $container;
    }

}
