<?php

namespace Elective\BehatContext\Context;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Elective\BehatContext\Context\RestContext
 */
class RestContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getKernel(): KernelInterface
    {
        return $this->kernel;
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
