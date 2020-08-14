<?php

namespace Lores\RestParamConverterBundle\Tests\Component\JMS\ExclusionStrategy;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Lores\RestParamConverterBundle\Component\JMS\ExclusionStrategy\SkipDoctrineReferenceStrategy;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Lores\RestParamConverterBundle\Component\JMS\ExclusionStrategy\SkipDoctrineReferenceStrategy
 */
final class SkipDoctrineReferenceStrategyTest extends MockeryTestCase
{
    /**
     * @var SkipDoctrineReferenceStrategy
     */
    private $strategy;

    /**
     * @var EntityManagerInterface|Mockery\MockInterface
     */
    private $manager;

    protected function setUp(): void
    {
        $this->manager = Mockery::mock(EntityManagerInterface::class);
        $this->strategy = new SkipDoctrineReferenceStrategy($this->manager);

        parent::setUp();
    }

    public function testSkipClass(): void
    {
        static::assertFalse($this->strategy->shouldSkipClass($this->mockClassMetaData(), $this->mockVoidContext()));
    }

    public function testSkipPropertyOnNoCurrentObjectMethod(): void
    {
        static::assertFalse(
            $this->strategy->shouldSkipProperty($this->mockPropertyMetaData(), $this->mockVoidContext())
        );
    }

    public function testSkipUnmanagedProperty(): void
    {
        $object = Mockery::mock();
        $this->manager
            ->shouldReceive('contains')
            ->with($object)
            ->once()
            ->andReturnFalse();

        static::assertFalse(
            $this->strategy->shouldSkipProperty($this->mockPropertyMetaData(), $this->mockContext($object))
        );
    }

    public function testSkipManagedProperty(): void
    {
        $object = Mockery::mock();
        $this->manager
            ->shouldReceive('contains')
            ->with($object)
            ->once()
            ->andReturnTrue();

        static::assertTrue(
            $this->strategy->shouldSkipProperty(
                $this->mockPropertyMetaData(),
                $this->mockContext($object)
            )
        );
    }

    private function mockClassMetaData()
    {
        return Mockery::mock(ClassMetadata::class);
    }

    private function mockPropertyMetaData()
    {
        return Mockery::mock(PropertyMetadata::class);
    }

    private function mockContext($object)
    {
        $context = Mockery::mock(Context::class);
        $visitor = Mockery::mock(new JsonDeserializationVisitor(), DeserializationVisitorInterface::class);
        $context
            ->shouldReceive('getVisitor')
            ->andReturn($visitor);
        $visitor
            ->shouldReceive('getCurrentObject')
            ->andReturn($object);

        return $context;
    }

    private function mockVoidContext()
    {
        $context = Mockery::mock(Context::class);
        $visitor = Mockery::mock(DeserializationVisitorInterface::class);
        $object = Mockery::mock();
        $context
            ->shouldReceive('getVisitor')
            ->andReturn($visitor);

        return $context;
    }
}
