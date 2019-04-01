<?php

namespace Elective\BehatContext\Context;

use Elective\BehatContext\Context\JsonContext;
use Elective\FormatterBundle\Parsers\Json;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
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
        } catch (Elective\FormatterBundle\Parsers\ParserException $e) {
            throw new \Exception(
                'Failed asserting that value is valid JSON'
            );
        }

        return true;
    }

    /**
     * @Then JSON nodes should contain:
     */
    public function jsonNodesShouldContain(TableNode $table)
    {
        foreach ($table->getRowsHash() as $node => $text) {
            $this->jsonNodeShouldContain($node, $text);
        }
    }

    /**
     * Checks, that given JSON node contains given value
     *
     * @Then JSON node :node should contain :text
     */
    public function jsonNodeShouldContain($node, $text, $content = null)
    {
        if (is_null($content)) {
            $content = $this->getContent();
        }

        if (!isset($content[$node])) {
            throw new \Exception(
                'Failed asserting that JSON
            . node '.$node.' is set'
            );
        }

        $text = $this->restContext->applyParametersToString($text);
        $actual = $content[$node];

        Assertions::assertRegexp("/$actual/", $text);
    }
}
