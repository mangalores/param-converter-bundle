<?php

namespace Lores\RestParamConverterBundle\Tests\Component\ParamConverter\ChainedHandler;

use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\DeserializeHandler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @covers \Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\DeserializeHandler
 */
final class DeserializeHandlerTest extends MockeryTestCase
{
    private const CONTENT = 'fooBarbaz';
    private const CLASS_NAME = 'Foo';
    private const DESERIALIZE_TYPE = 'json';
    private const ATTRIBUTE = 'bar';

    /**
     * @var DeserializeHandler
     */
    private $handler;

    /**
     * @var SerializerInterface|Mockery\MockInterface
     */
    private $serializer;

    /**
     * @var DeserializationContextFactoryInterface|Mockery\MockInterface
     */
    private $factory;

    /**
     * @var Request|Mockery\MockInterface
     */
    private $request;

    /**
     * @var ParamConverter|Mockery\MockInterface
     */
    private $configuration;

    protected function setUp(): void
    {
        $this->request = Mockery::mock(Request::class);
        $this->configuration = Mockery::mock(ParamConverter::class);
        $this->serializer = Mockery::mock(SerializerInterface::class);
        $this->factory = Mockery::mock(DeserializationContextFactoryInterface::class);
        $this->handler = new DeserializeHandler($this->serializer, $this->factory);

        parent::setUp();
    }

    public function testDeserialize(): void
    {
        $expected = Mockery::mock();

        $context = $this->mockContext();
        $this->setupTestCase($context);
        $this->setupExpectedValue($expected);

        $this->serializer
            ->shouldReceive('deserialize')
            ->with(self::CONTENT, self::CLASS_NAME, self::DESERIALIZE_TYPE, $context)
            ->once()
            ->andReturn($expected);

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
    }

    public function testFailOptionalParam(): void
    {
        $context = $this->mockContext();
        $this->setupTestCase($context);
        $this->setupExpectedValue(null);

        $this->configuration
            ->shouldReceive('isOptional')
            ->andReturnTrue();

        $this->serializer
            ->shouldReceive('deserialize')
            ->with(self::CONTENT, self::CLASS_NAME, self::DESERIALIZE_TYPE, $context)
            ->once()
            ->andThrow(RuntimeException::class);

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
    }

    public function testFailRequiredParam(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $context = $this->mockContext();
        $this->setupTestCase($context);

        $this->configuration
            ->shouldReceive('isOptional')
            ->andReturnFalse();

        $this->serializer
            ->shouldReceive('deserialize')
            ->with(self::CONTENT, self::CLASS_NAME, self::DESERIALIZE_TYPE, $context)
            ->once()
            ->andThrow(RuntimeException::class);

        $this->handler->handle($this->request, $this->configuration);
    }

    private function setupTestCase($context): void
    {
        $this->factory
            ->shouldReceive('createDeserializationContext')
            ->once()
            ->andReturn($context);

        $this->request
            ->shouldReceive('getContent')
            ->once()
            ->andReturn(self::CONTENT);

        $this->configuration
            ->shouldReceive('getClass')
            ->once()
            ->andReturn(self::CLASS_NAME);
    }

    public function setupExpectedValue($expected): void
    {
        $this->configuration
            ->shouldReceive('getName')
            ->once()
            ->andReturn(self::ATTRIBUTE);

        $this->request->attributes = Mockery::mock(ParameterBag::class);
        $this->request->attributes
            ->shouldReceive('set')
            ->with(self::ATTRIBUTE, $expected)
            ->once();
    }

    /**
     * @return DeserializationContext|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private function mockContext()
    {
        return Mockery::mock(DeserializationContext::class);
    }
}
