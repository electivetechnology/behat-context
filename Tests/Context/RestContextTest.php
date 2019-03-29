<?php

namespace Elective\BehatContext\Tests\Context;

use Elective\BehatContext\Context\RestContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Elective\BehatContext\Tests\Context\RestContext
 *
 * @author Kris Rybak <kris@electivegroup.com>
 */
class RestContextTest extends TestCase
{
    protected function getContext(): RestContext
    {
        $kernel = $this->createMock(KernelInterface::class);

        $context = $this->getMockBuilder(RestContext::class)
            ->setConstructorArgs([$kernel])
            ->getMock();

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
     */
    public function testSend($method = null, $url = null, $body = null)
    {
        $context = $this->getContext();
        $this->assertInstanceOf(RestContext::class, $context->send($method, $url, $body));
    }
}
