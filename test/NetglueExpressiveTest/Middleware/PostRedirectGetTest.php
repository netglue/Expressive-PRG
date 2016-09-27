<?php
/**
 * This file is part of the Expressive PRG Package
 * Copyright 2016 Net Glue Ltd (https://netglue.uk).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NetglueExpressiveTest\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Session\Container;
use NetglueExpressive\Middleware\PostRedirectGet as PRG;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\RedirectResponse;

class PostRedirectGetTest extends \PHPUnit_Framework_TestCase
{

    public function testSetAndGetSessionContainer()
    {
        $prg = new PRG;
        $this->assertInstanceOf(Container::class, $prg->getSessionContainer());

        $container = new Container('foo');
        $prg->setSessionContainer($container);
        $this->assertSame($container, $prg->getSessionContainer());
    }

    public function testFirstGetRequestWillHaveFalseAttribute()
    {
        $prg = new PRG;

        $req = new ServerRequest;
        $req = $req->withMethod('GET');

        $res = $prg($req, new Response, function($req, $res) {
            $this->assertFalse($req->getAttribute(PRG::KEY));
        });
    }

    public function testPostRequestWillReturnRedirect()
    {
        $prg = new PRG;

        $container = $prg->getSessionContainer();
        $this->assertNull($container->post);

        $req = new ServerRequest;
        $req = $req->withMethod('POST');
        $req = $req->withParsedBody([
            'foo' => 'bar',
        ]);

        $res = $prg($req, new Response, function($req, $res) {
            $this->fail('The next callable should not have been called. A redirect response should have been returned');
        });

        $this->assertInstanceOf(RedirectResponse::class, $res);
    }

    public function testGetRequestWithNonNullContainerWillHavePostAttribute()
    {
        $prg = new PRG;

        $container = $prg->getSessionContainer();
        $container->post = ['foo' => 'bar'];

        $req = new ServerRequest;

        $res = $prg($req, new Response, function($req, $res) {

            $attr = $req->getAttribute(PRG::KEY);
            $this->assertInternalType('array', $attr);
            $this->assertSame('bar', $attr['foo']);

        });

    }

    public function testUnmodifiedResponseForGetRequests()
    {
        $prg = new PRG;

        $req = new ServerRequest;
        $res = new Response;

        $return = $prg($req, $res);

        $this->assertSame($res, $return);
    }

}
