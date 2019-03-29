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
}
