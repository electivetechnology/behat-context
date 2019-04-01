<?php

namespace Elective\BehatContext\Tests\Context;

use Elective\BehatContext\Context\RestContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Elective\BehatContext\Tests\Context\RestContext
 *
 * @author Kris Rybak <kris@electivegroup.com>
 */
class RestContextTest extends TestCase
{
    protected function getContext(): RestContext
    {
        $kernel  = $this->createMock(KernelInterface::class);
        $context = new RestContext($kernel);

        return $context;
    }

    public function testConstructor()
    {
        $kernel = $this->createMock(KernelInterface::class);

        $context = $this->getMockBuilder(RestContext::class)
            ->setConstructorArgs([$kernel])
            ->getMock();

        $this->assertInstanceOf(KernelInterface::class, $context->getKernel());
    }

    public function testSetGetKernel()
    {
        $kernel = $this->createMock(KernelInterface::class);

        $context = $this->getMockBuilder(RestContext::class)
            ->setConstructorArgs([$kernel])
            ->getMock();

        $this->assertInstanceOf(RestContext::class, $context->setKernel($kernel));
        $this->assertInstanceOf(KernelInterface::class, $context->getKernel());
    }

    public function testSetGetResponse()
    {
        $response = $this->createMock(Response::class);
        $context = $this->getContext();

        $this->assertInstanceOf(RestContext::class, $context->setResponse($response));
        $this->assertInstanceOf(Response::class, $context->getResponse());
    }

    public function testSetGetRequest()
    {
        $request = $this->createMock(Request::class);
        $context = $this->getContext();

        $this->assertInstanceOf(RestContext::class, $context->setRequest($request));
        $this->assertInstanceOf(Request::class, $context->getRequest());
    }

    public function testSetGetClient()
    {
        $client = $this->createMock(Client::class);
        $context = $this->getContext();

        $this->assertInstanceOf(RestContext::class, $context->setClient($client));
        $this->assertInstanceOf(Client::class, $context->getClient());
    }

    public function setGetParametersProvider()
    {
        return array(
            array(
                array(
                    "invitations"   => array('FNo0GlFAgcnV'),
                    "questions"     => array('iseIVggkFkIY'),
                ),
                'iseIVggkFkIY',
                0,
                'questions'
            ),
            array(
                array(
                    "names"  => array('John', 'Donald'),
                    "books"  => array('iseIVggkFkIY'),
                ),
                'John',
                0,
                'names'
            ),
            array(
                array(
                    "names"     => array('John', 'Donald', 'Tim'),
                    "books"     => array('Great Gatsby', 'Ulises', 'the godfather'),
                    "cities"    => array('Paris', 'London', 'Tokio'),
                ),
                'the godfather',
                2,
                'books'
            ),
        );
    }

    /**
     * @dataProvider setGetParametersProvider
     */
    public function testSetGetParameters($parameters, $checkParamValue, $checkParamKey, $checkParamPrefix)
    {
        $context = $this->getContext();

        $this->assertInstanceOf(RestContext::class, $context->setParameters($parameters));
        $this->assertEquals($parameters, $context->getParameters());
        $this->assertEquals($checkParamValue, $context->getParameter($checkParamKey, $checkParamPrefix));
    }

    public function addParameterDataProvider()
    {
        return array(
            array('John', 'foo', 'names'),
            array('John', 0, 'names'),
            array('John', 0),
        );
    }

    /**
     * @dataProvider addParameterDataProvider
     */
    public function testAddParameter($value, $key, $prefix = null)
    {
        $context = $this->getContext();

        $this->assertInstanceOf(RestContext::class, $context->addParameter($value, $key, $prefix));
        $this->assertEquals($value, $context->getParameter($key, $prefix));
    }

    public function baseUrlProvider()
    {
        return array(
            array('/v1'),
            array('/v1/status'),
        );
    }
    /**
     * @dataProvider baseUrlProvider
     */
    public function testSetGetBaseUrl($baseUrl)
    {
        $context = $this->getContext();

        $this->assertInstanceOf(RestContext::class, $context->setBaseUrl($baseUrl));
        $this->assertEquals($baseUrl, $context->getBaseUrl());
    }

    public function iSendARequestToDataProvider()
    {
        return array(
            array('GET', '/'),
            array('POST', '/'),
            array('PATCH', '/'),
            array('DELETE', '/v1/status'),
            array('PUT', '/v1'),
            array('GET', '/v1/users/{users:1}'),
        );
    }

    /**
     * @dataProvider iSendARequestToDataProvider
     * @expectedException GuzzleHttp\Exception\RequestException
     */
    public function testISendARequestTo($method, $url)
    {
        $context = $this->getContext();
        $this->assertInstanceOf(RestContext::class, $context->iSendARequestTo($method, $url));
    }

    public function sendDataProvider()
    {
        return array(
            array('DELETE', '/v1/status', 'string'),
            array(null, '/v1/status', 'string'),
            array(null, null, 'string'),
            array(null, null, null),
        );
    }

    /**
     * @dataProvider sendDataProvider
     * @expectedException GuzzleHttp\Exception\RequestException
     */
    public function testSend($method = null, $url = null, $body = null)
    {
        $context = $this->getContext();
        $this->assertInstanceOf(RestContext::class, $context->send($method, $url, $body));
    }

    public function applyParametersToStringDataProvider()
    {
        return array(
            array(
                '/v1/users/{users:0}/friends/{users:2}',
                ['users' => ['john', 'doe', 'donald', 'trump']],
                '/v1/users/john/friends/donald'
            ),
            array(
                'The quick {colour:0} {animal:0} jumps over the lazy {animal:1}.',
                ['colour' => ['brown'], 'animal' => ['fox', 'dog']],
                'The quick brown fox jumps over the lazy dog.'
            ),
        );
    }

    /**
     * @dataProvider applyParametersToStringDataProvider
     */
    public function testApplyParametersToString($string, $parameters = array(), $expected)
    {
        $context = $this->getContext();
        $context->setParameters($parameters);
        $ret = $context->applyParametersToString($string);
        $this->assertTrue(is_string($ret));
        $this->assertEquals($expected, $ret);
    }

    public function getParametersFromStringDataProvider()
    {
        return array(
            array(
                '/v1/users/{users:0}/emails/{emails:2}',
                2,
                [['name' => 'users', 'index' => 0], ['name' => 'emails', 'index' => 2]]
            ),
            array(
                '{users:5}/{emails:2}/{names:1}/{names:3}',
                4,
                [['name' => 'users', 'index' => 5], ['name' => 'emails', 'index' => 2], ['name' => 'names', 'index' => 1], ['name' => 'names', 'index' => 3]]
            ),
            array('hello world', 0),
        );
    }

    /**
     * Goes through a string and finds embedded parameters
     *
     * @dataProvider getParametersFromStringDataProvider
     */
    public function testGetParametersFromString($str, $numberOfParams, $params = [])
    {
        $context = $this->getContext();
        $ret    = $context->getParametersFromString($str);

        $this->assertTrue(is_array($ret));
        $this->assertCount($numberOfParams, $ret);

        foreach ($params as $key => $parameter) {
            $this->assertTrue($ret[$key][1] == $parameter['name']);
            $this->assertTrue($ret[$key][3] == $parameter['index']);
        }
    }
}
