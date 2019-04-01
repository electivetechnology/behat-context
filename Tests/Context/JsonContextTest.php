<?php

namespace Elective\BehatContext\Tests\Context;

use Elective\BehatContext\Context\JsonContext;
use PHPUnit\Framework\TestCase;
use Behat\Gherkin\Node\TableNode;

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

    public function isValidJsonPassDataProvider()
    {
        return array(
            array('{"foo": "bar"}'),
            array('[{"foo": "bar"}, {"moo": "loo"}]'),
        );
    }

    /**
     * @dataProvider isValidJsonPassDataProvider
     */
    public function testIsValidJsonPass($json)
    {
        $context = $this->getContext();

        $this->assertTrue($context->isValidJson($json));
    }

    public function isValidJsonFailDataProvider()
    {
        return array(
            array('{"foo": "bar",}'),
            array('[{"foo": "bar"}, {"moo": loo"}]'),
            array('abc'),
        );
    }

    /**
     * @dataProvider isValidJsonFailDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Failed asserting that value is valid JSON
     */
    public function testIsValidJsonFail($json)
    {
        $context = $this->getContext();

        $this->assertTrue($context->isValidJson($json));
    }

    public function jsonNodesShouldContainDataProvider()
    {
        return array(
            array(['12' => ['status', '200']], '{"status": "200"}'),
            array(['6' => ['foo', 'bar']], '{"foo": "bar"}'),
            array(['86' => ['moo', 'loo']], '{"foo": "bar", "moo": "loo"}'),
        );
    }

    /**
     * @dataProvider jsonNodesShouldContainDataProvider
     */
    public function testJsonNodesShouldContain($table, $content)
    {
        $context = $this->getContext();
        $table = new TableNode($table);
        $context->setContent($content);
        $this->assertTrue($context->jsonNodesShouldContain($table));
    }
}
