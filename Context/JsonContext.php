<?php

namespace Elective\BehatContext\Context;

use Elective\BehatContext\Context\JsonContext;
use Elective\FormatterBundle\Parsers\Json;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as Assertions;

/**
 * Elective\BehatContext\Context\JsonContext
 */
class JsonContext implements Context
{
    /**
     * @var array
     */
    private $content;

    public function setContent($content): self
    {
        $this->content = $this->toJson($content);

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    private function toJson($string)
    {
        $json = Json::decode($string, true);

        return $json;
    }

    /**
     * @Then :value is valid JSON
     */
    public function isValidJson($value)
    {
        try {
            $actual = $this->toJson($value);     
        } catch (\Exception $e) {
            throw new \Exception(
                'Failed asserting that value is valid JSON'
            );
        }

        return true;
    }

    /**
     * @Then JSON nodes should contain:
     */
    public function jsonNodesShouldContain(TableNode $table, $content = [])
    {
        foreach ($table->getRowsHash() as $node => $text) {
            $this->jsonNodeShouldContain($node, $text, $content);
        }

        return true;
    }

    /**
     * Checks, that given JSON node contains given value
     *
     * @Then JSON node :node should contain :text
     */
    public function jsonNodeShouldContain($node, $text, $content = [])
    {
        if (empty($content)) {
            $content = $this->getContent();
        }

        if (!isset($content[$node])) {
            throw new \Exception(
                'Failed asserting that JSON
            . node '.$node.' is set'
            );
        }

        $actual = $content[$node];

        Assertions::assertRegexp("/$actual/", $text);

        return true;
    }

    /**
     * @Then JSON node :node should exist
     */
    public function theJsonNodeShouldExist($node, $content = [])
    {
        if (empty($content)) {
            $content =  $this->getContent();
        }

        if(!isset($content[$node])) {
            throw new \Exception(
                'Failed asserting that JSON '
                .'node '.$node.' is set'
            );
        }

        return true;
    }
}
