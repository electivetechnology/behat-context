<?php

namespace Elective\BehatContext;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\KernelInterface;

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
}
