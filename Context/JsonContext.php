<?php

namespace Elective\BehatContext\Context;

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

    /**
     * @var Context
     */
    private $restContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->restContext = $environment->getContext('Elective\BehatContext\Context\RestContext');
    }

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
    public function jsonNodesShouldContain(TableNode $table, $content = [], int $rowNumber = null)
    {
        if (is_null($rowNumber)) {
            foreach ($table->getRowsHash() as $node => $text) {
                $this->jsonNodeShouldContain($node, $text, $content);
            }
        } else {
            if (empty($content)) {
                $content = $this->getContent();
            }

            // content is 0 indexed, we will use -1 index instead for result set
            // since it's much easier for contextual processing
            $rowNumber = $rowNumber -1;

            if (!isset($content[$rowNumber])) {
                throw new \Exception(
                    'Result with offset ' . $rowNumber
                    . ' does not exist'
                );
            }

            foreach ($table->getRowsHash() as $node => $text) {
                $this->jsonNodeShouldContain($node, $text, $content[$rowNumber]);
            }
        }

        return true;
    }

    /**
     * @Then JSON nodes should not contain:
     */
    public function jsonNodesShouldNotContain(TableNode $table, $content = [])
    {
        foreach ($table->getRowsHash() as $node => $text) {
            $this->jsonNodeShouldNotContain($node, $text, $content);
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

        if (!is_null($this->restContext)) {
            $text = $this->restContext->applyParametersToString($text);
        }

        $actual = $content[$node];

        // Covert result to string for comparison
        if (is_bool($actual)) {
            $actual = ($actual) ? 'true' : 'false';
        }

        Assertions::assertEquals($actual, $text);

        return true;
    }

    /**
     * Checks, that given JSON node contains given value
     *
     * @Then JSON node :node should not contain :text
     */
    public function jsonNodeShouldNotContain($node, $text, $content = [])
    {
        if (empty($content)) {
            $content = $this->getContent();
        }

        if (!isset($content[$node])) {
            throw new \Exception(
                'Failed asserting that JSON
            . node '.$node.' is set or is not NULL'
            );
        }

        if (!is_null($this->restContext)) {
            $text = $this->restContext->applyParametersToString($text);
        }

        $actual = $content[$node];

        if ($text !== "NULL") {
            Assertions::assertNotRegExp("/$actual/", $text);
        }

        return true;
    }

    public function theJsonNodesShouldExist($node, $content = [])
    {
        if (empty($content)) {
            $content = $this->getContent();
        }

        foreach ($content as $object) {
            $this->theJsonNodeShouldExist($node, $object);
        }

        return true;
    }

    /**
     * @Then JSON node :node should exist
     */
    public function theJsonNodeShouldExist($node, $content = [])
    {
        if (empty($content)) {
            $content = $this->getContent();
        }

        if(!array_key_exists($node, $content)) {
            throw new \Exception(
                'Failed asserting that JSON '
                .'node '.$node.' is set'
            );
        }

        return true;
    }

    /**
     * @Then there should be :numberOf JSON results
     */
    public function thereShouldBeJsonResults($numberOf, $content = [])
    {
        if (empty($content)) {
            $content = $this->getContent();
        }

        Assertions::assertCount((int) $numberOf, $content);

        return $numberOf;
    }

     /**
     * @Then response :rowNumber JSON object nodes should contain:
     */
    public function responseJsonObjectNodesShouldContain($rowNumber, TableNode $table)
    {
        $json = $this->getContent();

        if (!is_array($json)) {
            throw new \Exception(
                'Expected result set to be an array of objects'
            );
        }

        if (!is_numeric($rowNumber)) {
            switch ($rowNumber) {
                case 'first':
                    $rowNumber = array_key_first($json);
                    break;
                case 'last':
                    $rowNumber = array_key_last($json);
                    break;
            }
        }

        if (!isset($json[$rowNumber])) {
            throw new \Exception(
                'Result with offset ' . $rowNumber
                . ' does not exist'
            );
        }

        foreach ($table->getRowsHash() as $node => $text) {
            $this->jsonNodeShouldContain($node, $text, $json[$rowNumber]);
        }
    }
}
