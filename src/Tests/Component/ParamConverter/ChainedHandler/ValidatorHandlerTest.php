<?php

namespace Lores\RestParamConverterBundle\Tests\Component\ParamConverter\ChainedHandler;

use JMS\Serializer\Exception\ValidationFailedException;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandlerInterface;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ValidatorHandler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @covers \Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ValidatorHandler
 */
final class ValidatorHandlerTest extends MockeryTestCase
{
    private const ATTRIBUTE = 'foo';

    /**
     * @var ValidatorHandler
     */
    private $handler;

    /**
     * @var Request|Mockery\MockInterface
     */
    private $request;

    /**
     * @var ParamConverter|Mockery\MockInterface
     */
    private $configuration;

    /**
     * @var ValidatorInterface|Mockery\MockInterface
     */
    private $validator;

    protected function setUp(): void
    {
        $this->request = Mockery::mock(Request::class);
        $this->configuration = Mockery::mock(ParamConverter::class);
        $this->validator = Mockery::mock(ValidatorInterface::class);
        $this->handler = new ValidatorHandler($this->validator);

        parent::setUp();
    }

    public function testPassIfNotSet(): void
    {
        $this->configuration
            ->shouldReceive('getName')
            ->once()
            ->andReturn(self::ATTRIBUTE);
        $this->request->attributes = Mockery::mock(ParameterBag::class);
        $this->request->attributes
            ->shouldReceive('has')
            ->with(self::ATTRIBUTE)
            ->andReturnFalse();

        $next = Mockery::mock(ChainedHandlerInterface::class);
        $next
            ->shouldReceive('handle')
            ->with($this->request, $this->configuration)
            ->once()
            ->andReturnTrue();

        $this->handler->setNext($next);

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
    }

    public function testValidPass(): void
    {
        $this->setUpTestCase();

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
    }

    public function testInvalidPass(): void
    {
        $this->expectException(ValidationFailedException::class);
        $this->setUpTestCase(1);

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
    }

    private function setUpTestCase(?int $count = 0): void
    {
        $mock = Mockery::mock();

        $this->configuration
            ->shouldReceive('getName')
            ->once()
            ->andReturn(self::ATTRIBUTE);

        $this->request->attributes = Mockery::mock(ParameterBag::class);
        $this->request->attributes
            ->shouldReceive('has')
            ->with(self::ATTRIBUTE)
            ->andReturnTrue();
        $this->request->attributes
            ->shouldReceive('get')
            ->with(self::ATTRIBUTE)
            ->andReturn($mock);
        $violationList = Mockery::mock(ConstraintViolationListInterface::class);

        $violationList
            ->shouldReceive('count')
            ->andReturn($count);
        $this->validator
            ->shouldReceive('validate')
            ->with($mock)
            ->once()
            ->andReturn($violationList);
    }
}
