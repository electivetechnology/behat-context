<?php

namespace Elective\BehatContext\Tests\Context;

use Elective\BehatContext\Context\JsonContext;
use PHPUnit\Framework\TestCase;

/**
 * Elective\BehatContext\Tests\Context\JsonContextTest
 *
 * @author Kris Rybak <kris@electivegroup.com>
 */
class JsonContextTest extends TestCase
{
    protected function getContext(): JsonContext
    {
        $context = new JsonContext();

        return $context;
    }

    public function testSetGetContent()
    {
        $context = $this->getContext();
        $content = '{"foo": "bar"}';

        $this->assertInstanceOf(JsonContext::class, $context->setContent($content));
        $this->assertTrue(is_array($context->getContent()));
    }
}
