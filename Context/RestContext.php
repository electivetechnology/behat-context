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
}
