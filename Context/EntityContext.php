<?php

namespace Elective\BehatContext\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as Assertions;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Elective\BehatContext\Context\EntityContext
 */
class EntityContext implements Context
{
    /**
     * @var Context
     */
    private $restContext;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->restContext = $environment->getContext('Elective\BehatContext\Context\RestContext');
    }

    public function thereIsEntity($entity, $row, $prefix = null, array $injectors = array())
    {
        if (!class_exists($entity)) {
            throw new \Exception(
                'Could not find entity class: ' . $entity
            );
        }

        $object = new $entity;

        if (!in_array("Ucc\Model\ModelInterface", class_implements($object))) {
            throw new \Exception(
                'Method can only load entities that implement Ucc\Model\ModelInterface'
            );
        }

        $object->fromArray($row);

        // Add injectors
        foreach ($injectors as $injector) {
            if (isset($injector['setter']) && isset($injector['object']) && method_exists($object, $injector['setter'])) {
                $method = $injector['setter'];
                $object->$method($injector['object']);
            }
        }

        $this->manager->persist($object);
        $this->manager->flush();

        $this->restContext->addParameter($object->getId(), null, $prefix);
    }
}
