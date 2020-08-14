<?php

namespace Lores\RestParamConverterBundle\Tests\Component\JMS;

use Doctrine\ORM\EntityManagerInterface;
use Lores\RestParamConverterBundle\Component\JMS\ExclusionStrategy\SkipDoctrineReferenceStrategy;
use Lores\RestParamConverterBundle\Component\JMS\SkipDoctrineContextFactory;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Lores\RestParamConverterBundle\Component\JMS\SkipDoctrineContextFactory
 */
final class SkipDoctrineContextFactoryTest extends MockeryTestCase
{
    public function testCreate(): void
    {
        $manager = Mockery::mock(EntityManagerInterface::class);
        $factory = new SkipDoctrineContextFactory($manager);

        $context = $factory->createDeserializationContext();

        static::assertInstanceOf(SkipDoctrineReferenceStrategy::class, $context->getExclusionStrategy());
    }
}
