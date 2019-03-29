<?php

namespace Elective\BehatContext\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\KernelInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

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

    public function __construct(KernelInterface $kernel, $baseUrl = '')
    {
        $this->kernel   = $kernel;
        $this->baseUrl  = $baseUrl;
        $this->client   = $this->createClient();
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

    public function createClient()
    {
        return new Client(
            [
                // Base URI is used with relative requests
                'base_url' => $this->getBaseUrl(),
            ]
        );
    }

    /**
     * @When I send a :method request to :url
     */
    public function iSendARequestTo($method, $url): self
    {
        $this->send($method, $url);

        return $this;
    }

    /**
     * Sends request
     */
    public function send($method = "GET", $url = "", string $body = null, $headers = array()): self
    {
        $this->request = new Request($method, $this->baseUrl . $url, $headers, $body);

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
}
