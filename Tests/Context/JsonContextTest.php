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
     */
    public function testIsValidJsonFail($json)
    {
        $context = $this->getContext();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed asserting that value is valid JSON');
        $this->assertTrue($context->isValidJson($json));
    }

    public function jsonNodesShouldContainDataProvider()
    {
        return array(
            array(['12' => ['status', '200']], ["status" => "200"]),
            array(['12' => ['status', '200']], ["status" => "200"], null, true),
            array(['12' => ['status', '200']], [["status" => "200"]], 1, true),
            array(['12' => ['status', '200']], [["foo"], ["status" => "200"]], 2, true),
            array(['6' => ['foo', 'bar']], ["foo" => "bar"]),
            array(['86' => ['moo', 'loo']], ["foo" => "bar", "moo" => "loo"]),
        );
    }

    /**
     * @dataProvider jsonNodesShouldContainDataProvider
     */
    public function testJsonNodesShouldContain($table, $content, $rowNumber = null, $useContentSetter = false)
    {
        $context = $this->getContext();
        $table = new TableNode($table);
        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertTrue($context->jsonNodesShouldContain($table, $content, $rowNumber));
    }

    public function jsonNodesShouldContainFailDataProvider()
    {
        return array(
            array(['12' => ['status', '200']], ["status" => "200"], 1, true),
            array(['12' => ['status', '200']], [["status" => "200"]], 2, true),
            array(['12' => ['status', '200']], [["foo"], ["status" => "200"]], 1, true),
        );
    }

    /**
     * @dataProvider jsonNodesShouldContainFailDataProvider
     */
    public function testJsonNodesShouldContainFail($table, $content, $rowNumber = null, $useContentSetter = false)
    {
        $this->expectException(\Exception::class);
        $context = $this->getContext();
        $table = new TableNode($table);
        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertTrue($context->jsonNodesShouldContain($table, $content, $rowNumber));
    }

    public function thereShouldBeJsonResultsDataProvider()
    {
        return array(
            array(2, ["foo", "bar"]),
            array(2, ["foo", "bar"], true),
            array(3, ["foo" => "foo", "bar" => "bar", "loo"]),
            array(3, ["foo" => "foo", "bar" => "bar", "loo"], true),
        );
    }

    /**
     * @dataProvider thereShouldBeJsonResultsDataProvider
     */
    public function testThereShouldBeJsonResults($numberOf, $content, $useContentSetter = false)
    {
        $context = $this->getContext();

        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertEquals($numberOf, $context->thereShouldBeJsonResults($numberOf, $content));
    }

    public function theJsonNodeShouldExistDataProvider()
    {
        return array(
            array('foo', ["foo" => "bar"]),
            array('foo', ["foo" => null]),
            array('foo', ["bar" => "bar", "loo" => "moo", "foo" => "foo"]),
            array('foo', ["bar" => "bar", "loo" => "moo", "foo" => "foo"], true),
        );
    }

    /**
     * @dataProvider theJsonNodeShouldExistDataProvider
     */
    public function testTheJsonNodeShouldExist($node, $content, $useContentSetter = false)
    {
        $context = $this->getContext();

        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertTrue($context->theJsonNodeShouldExist($node, $content));
    }

    public function theJsonNodeShouldExistFailDataProvider()
    {
        return array(
            array('xxx', [1,2]),
            array('ooo', ["bar" => ["bar", "foo"], "loo" => "moo", "foo" => "foo"]),
        );
    }

    /**
     * @dataProvider theJsonNodeShouldExistFailDataProvider
     */
    public function testTheJsonNodeShouldExistFail($node, $content)
    {
        $this->expectException(\Exception::class);
        $context = $this->getContext();

        $this->assertTrue($context->theJsonNodeShouldExist($node, $content));
    }

    public function theJsonNodesShouldExistDataProvider()
    {
        return array(
            array('foo', [["foo" => "foo"], ["foo" => "bar"]]),
            array('foo', [["foo" => "foo"], ["foo" => "bar"]], true),
            array('foo', [["bar" => "bar", "foo" => "foo"], ["foo" => "bar", "bar" => "bar"]]),
            array('foo', [["bar" => "bar", "foo" => "foo"], ["foo" => "bar", "bar" => "bar"]], true),
        );
    }

    /**
     * @dataProvider theJsonNodesShouldExistDataProvider
     */
    public function testTheJsonNodesShouldExist($node, $content, $useContentSetter = false)
    {
        $context = $this->getContext();

        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertTrue($context->theJsonNodesShouldExist($node, $content));
    }




    public function jsonNodesShouldNotContainDataProvider()
    {
        return array(
            array(['12' => ['status', '200']], ["status" => "30"]),
            array(['12' => ['status', '200']], ["status" => "300"], null, true),
            array(['6' => ['foo', 'bar']], ["foo" => "foo"]),
            array(['86' => ['moo', 'loo']], ["foo" => "bar", "moo" => "moo"]),
        );
    }

    /**
     * @dataProvider jsonNodesShouldNotContainDataProvider
     */
    public function testJsonNodesShouldNotContain($table, $content, $rowNumber = null, $useContentSetter = false)
    {
        $context = $this->getContext();
        $table = new TableNode($table);
        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertTrue($context->jsonNodesShouldNotContain($table, $content, $rowNumber));
    }



    public function jsonNodeShouldNotContainDataProvider()
    {
        return array(
            array('foo', 'foo', ["foo" => "bar"]),
            array('foo', 'foo', ["foo" => "bar"], true),
            array('foo', 'bar', ["foo" => "foo", "bar" => "bar"]),
            array('foo', 'bar', ["foo" => "foo", "bar" => "bar"], true),
            array('foo', 'NULL', ["foo" => "bar"]),
            array('foo', 'NULL', ["foo" => "bar"], true),
        );
    }

    /**
     * @dataProvider jsonNodeShouldNotContainDataProvider
     */
    public function testJsonNodeShouldNotContain($node, $text, $content = [], $useContentSetter = false)
    {
        $context = $this->getContext();

        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertTrue($context->jsonNodeShouldNotContain($node, $text, $content));
    }

    public function jsonNodeShouldNotContainfailDataProvider()
    {
        return array(
            array('foo', 'foo', ["bar" => "foo"]),
            array('foo', 'foo', ["bar" => "foo"], true),
        );
    }

    /**
     * @dataProvider jsonNodeShouldNotContainfailDataProvider
     */
    public function testJsonNodeShouldNotContainFail($node, $text, $content = [], $useContentSetter = false)
    {
        $this->expectException(\Exception::class);
        $context = $this->getContext();

        if ($useContentSetter) {
            $context->setContent(json_encode($content));

            $content = null;
        }

        $this->assertTrue($context->jsonNodeShouldNotContain($node, $text, $content));
    }
}
