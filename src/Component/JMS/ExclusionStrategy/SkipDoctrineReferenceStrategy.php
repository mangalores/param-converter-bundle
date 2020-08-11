<?php

namespace Lores\RestParamConverterBundle\Component\JMS\ExclusionStrategy;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

final class SkipDoctrineReferenceStrategy implements ExclusionStrategyInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function shouldSkipClass(ClassMetadata $metadata, Context $context): bool
    {
        return false;
    }

    public function shouldSkipProperty(PropertyMetadata $property, Context $context): bool
    {
        $visitor = $context->getVisitor();

        // implemented by both xml and json, but not declared in interfaces
        if (\method_exists($visitor, 'getCurrentObject')) {
            return $this->manager->contains($visitor->getCurrentObject());
        }

        return false;
    }
}
