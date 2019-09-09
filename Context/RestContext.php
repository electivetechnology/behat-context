<?php

namespace Elective\BehatContext\Context;

use Elective\BehatContext\Context\JsonContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\HttpKernel\KernelInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\Assert as Assertions;

/**
 * Elective\BehatContext\Context\RestContext
 */
class RestContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var array
     */
    private $existingItems = [];

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $headers;

    /**
     * @var Context
     */
    private $jsonContext;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel       = $kernel;
        $this->baseUrl      = getenv('APP_DSN');
        $this->parameters   = array();
        $this->headers      = array();
        $this->client       = $this->createClient();
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
    
        $this->jsonContext = $environment->getContext('Elective\BehatContext\Context\JsonContext');
    }

    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    public function setKernel(KernelInterface $kernel): self
    {
        $this->kernel = $kernel;

        return $this;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): self
    {
        foreach ($parameters as $prefix => $params) {
            foreach ($params as $key => $value) {
                $this->addParameter($value, $key, $prefix);
            }
        }

        return $this;
    }

    public function addParameter($value, $key = null, $prefix = null)
    {
        if ($prefix) {
            if ($key) {
                $this->parameters[$prefix][$key] = $value;
            } else {
                $this->parameters[$prefix][] = $value;
            }
        } else {
            if ($key) {
                $this->parameters['default'][$key] = $value;
            } else {
                $this->parameters['default'][] = $value;
            }
        }

        return $this;
    }

    public function getParameter($key, $prefix = null)
    {
        if (!$prefix) {
            if (!isset($this->parameters['default'][$key])) {
                return null;
            }

            return $this->parameters['default'][$key];
        }

        if (!isset($this->parameters[$prefix][$key])) {
            return null;
        }

        return $this->parameters[$prefix][$key];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): self
    {
        foreach ($headers as $key => $value) {
            $this->addHeader($key, $value);
        }

        return $this;
    }

    public function addHeader($key, $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function getHeader($key)
    {
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }

        return null;
    }

    public function createClient()
    {
        return new Client(
            [
                // Base URI is used with relative requests
                'base_url' => $this->getBaseUrl(),
            ]
        );
    }

    public function addExistingItem(string $id, $value)
    {
        $this->existingItems[$id] = $value;

        return $this;
    }

    public function getExistingItem(string $id)
    {
        if (isset($this->existingItems[$id])) {
            return $this->existingItems[$id];
        }

        return null;
    }

    public function getExistingItemFirstKey()
    {
        return array_key_first($this->existingItems);
    }

    /**
     * @When I send a :method request to :url
     */
    public function iSendARequestTo($method, $url): self
    {
        return $this->iSendARequestToWithBody($method, $url, null);
    }

    /**
     * @When I send a :method request to :url with body:
     */
    public function iSendARequestToWithBody($method, $url, PyStringNode $string = null): self
    {
        if (!is_null($string)) {
            $string = $string->__toString();
        }

        return $this->send($method, $url, $string);
    }

    /**
     * @When I send a :method request to :url with existing items as parameters
     */
    public function iSendARequestToWithExistingItemsAsParameters($method, $url)
    {
        $this->iSendARequestToWithExistingItemsAsParametersAndBody($method, $url, null);
    }

    /**
     * @When I send a :method request to :url with existing items as parameters and body
     */
    public function iSendARequestToWithExistingItemsAsParametersAndBody($method, $url, PyStringNode $string = null)
    {
        $this->iSendARequestToWithBody($method, $url, $string);
    }

    /**
     * Sends request
     */
    public function send($method = "GET", $url = "", string $body = null, $headers = array()): self
    {
        $headers = array_merge($headers, $this->getHeaders());

        $this->request = new Request($method, $this->baseUrl . $this->applyParametersToString($url), $headers, $this->applyParametersToString($body));

        try {
            $this->response = $this->getClient()->send($this->request);
        } catch (RequestException $e) {
            $this->response = $e->getResponse();

            if (null === $this->response) {
                throw $e;
            }
        } catch (ClientException $e) {
            $this->response = $e->getResponse();

            if (null === $this->response) {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Applies parameters to string. Replaces parameter placeholder with its corresponding value
     *
     * @param   $str string  String to process
     * @return  string       String with values over parameters
     */
    public function applyParametersToString($str = ""): ?string
    {
        $parameters = $this->getParametersFromString($str);

        // Match the parameter with it's value
        foreach ($parameters as $paramSpec) {
            $val = $this->getParameterValueFromSpec($paramSpec);
            if (!is_null($val)) {
                $str = str_replace($paramSpec[0], $val, $str);
            }
        }

        return $str;
    }

    /**
     * Extracts parameters from string
     *
     * @param $str  string
     * @return array        List of matched parameters
     */
    public function getParametersFromString($str = ''): array
    {
        $re = '/{(\w*)(:)*(\d*)}/m';
        preg_match_all($re, $str, $parameters, PREG_SET_ORDER, 0);

        return $parameters;
    }

    /**
     * Matches parameter spec with it's value in parameters
     *
     * @param $paramSpec array
     * @return string Value of that parameter
     */
    public function getParameterValueFromSpec(array $paramSpec)
    {
        $val = null;

        if (!empty($paramSpec[2])) {
            $val = $this->getParameter($paramSpec[3], $paramSpec[1]);
        } else {
            $val = $this->getParameter($paramSpec[1]);
        }

        return $val;
    }

    /**
     * @Then the response code should be :code
     */
    public function theResponseCodeShouldBe($code)
    {
        Assertions::assertEquals($code, $this->getResponse()->getStatusCode());
    }

    /**
     * @Then the response should be a valid JSON
     */
    public function theResponseShouldBeAValidJson()
    {
        // Start at the beginning
        $this->response->getBody()->rewind();

        // Get content
        $content = $this->response->getBody()->getContents();

        // Check is valid JSON
        $this->jsonContext->isValidJson($content);
        $this->jsonContext->setContent($content);
    }

    /**
     * @Then the response header :key should be equal to :value
     */
    public function theResponseHeaderShouldBeEqualTo($key, $value)
    {
        $actual = $this->response->getHeader($key);
        Assertions::assertSame($value, $actual[0]);
    }

    /**
     * @Then the response JSON nodes should contain:
     */
    public function theResponseJsonNodesShouldContain(TableNode $table)
    {
        $this->prepareJsonContextContent();

        // Check content
        $this->jsonContext->jsonNodesShouldContain($table);
    }

    /**
     * @Then the response JSON nodes should not contain:
     */
    public function theResponseJsonNodesShouldNotContain(TableNode $table)
    {
        $this->prepareJsonContextContent();

        // Check content
        $this->jsonContext->jsonNodesShouldNotContain($table);
    }

    /**
     * @Then each response JSON object should have node :node
     */
    public function eachResponseJsonObjectShouldHaveNode($node)
    {
        $this->prepareJsonContextContent();

        // Check content
        $this->jsonContext->theJsonNodesShouldExist($node);
    }

    /**
     * @Then the response JSON node :node should exist
     */
    public function theResponseJsonNodeShouldExist($node)
    {
        $this->prepareJsonContextContent();

        // Check content
        $this->jsonContext->theJsonNodeShouldExist($node);
    }

    /**
     * @Then the response should have :numberOf JSON results
     */
    public function theResponseShouldHaveJsonResults($numberOf)
    {
        $this->prepareJsonContextContent();

        // Check content
        $this->jsonContext->thereShouldBeJsonResults($numberOf);
    }

    public function prepareJsonContextContent()
    {
        // Start at the beginning
        $this->response->getBody()->rewind();

        // Get content
        $content = $this->response->getBody()->getContents();

        // Check is valid JSON
        $this->jsonContext->isValidJson($content);
        $this->jsonContext->setContent($this->applyParametersToString($content));
    }

    /**
     * @Then the response :rowNumber JSON object nodes should contain:
     */
    public function theResponseJsonObjectNodesShouldContain($rowNumber, TableNode $table)
    {
        $this->prepareJsonContextContent();

        // Check content
        $this->jsonContext->jsonNodesShouldContain($table, [], $rowNumber);
    }
}
