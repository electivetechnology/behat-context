<?php

namespace Elective\BehatContext\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\KernelInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

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

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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
    public function send($method = "GET", $url = "", $body = null): self
    {
        return $this;
    }
}
