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
    private $content;

    public function setContent($content)
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
}
