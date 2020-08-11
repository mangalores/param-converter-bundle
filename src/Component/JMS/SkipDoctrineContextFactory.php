<?php

namespace Lores\RestParamConverterBundle\Component\JMS;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use Lores\RestParamConverterBundle\Component\JMS\ExclusionStrategy\SkipDoctrineReferenceStrategy;

final class SkipDoctrineContextFactory implements DeserializationContextFactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function createDeserializationContext(): DeserializationContext
    {
        $context = new DeserializationContext();
        $context->addExclusionStrategy(new SkipDoctrineReferenceStrategy($this->manager));

        return $context;
    }
}
