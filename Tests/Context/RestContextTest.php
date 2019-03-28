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
    public function testConstructor()
    {
        $kernel = $this->createMock(KernelInterface::class);

        $context = $this->getMockBuilder(RestContext::class)
            ->setConstructorArgs([$kernel])
            ->getMock();

        $this->assertInstanceOf(KernelInterface::class, $context->getKernel());
    }
}
