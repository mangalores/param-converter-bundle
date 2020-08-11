<?php

namespace Lores\RestParamConverterBundle\Tests\Component\ParamConverter\ChainedHandler;

use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandlerInterface;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\DelegateHandler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\DelegateHandler
 * @covers \Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandler
 */
final class DelegateHandlerTest extends MockeryTestCase
{
    /**
     * @var DelegateHandler
     */
    private $handler;

    /**
     * @var ParamConverterInterface|Mockery\MockInterface
     */
    private $converter;

    /**
     * @var Request|Mockery\MockInterface
     */
    private $request;

    /**
     * @var ParamConverter|Mockery\MockInterface
     */
    private $configuration;

    /**
     * @var ChainedHandlerInterface|Mockery\MockInterface
     */
    private $next;

    protected function setUp(): void
    {
        $this->request = Mockery::mock(Request::class);
        $this->configuration = Mockery::mock(ParamConverter::class);
        $this->next = Mockery::mock(ChainedHandlerInterface::class);
        $this->converter = Mockery::mock(ParamConverterInterface::class);
        $this->handler = new DelegateHandler($this->converter);

        parent::setUp();
    }

    public function testDelegateWithNextHandler(): void
    {
        $this->converter
            ->shouldReceive('supports')
            ->once()
            ->with($this->configuration)
            ->andReturnTrue();

        $this->converter
            ->shouldReceive('apply')
            ->once()
            ->with($this->request, $this->configuration)
            ->andReturnTrue();

        $this->next
            ->shouldReceive('handle')
            ->once()
            ->with($this->request, $this->configuration)
            ->andReturnTrue();

        $this->handler->setNext($this->next);

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
    }

    public function testDelegateWithoutNextHandler(): void
    {
        $this->converter
            ->shouldReceive('supports')
            ->once()
            ->with($this->configuration)
            ->andReturnTrue();

        $this->converter
            ->shouldReceive('apply')
            ->once()
            ->with($this->request, $this->configuration)
            ->andReturnTrue();

        $this->next
            ->shouldReceive('handle')
            ->never();

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
    }

    public function testInterruptOnNoSupport(): void
    {
        $this->converter
            ->shouldReceive('supports')
            ->once()
            ->with($this->configuration)
            ->andReturnFalse();

        $this->next
            ->shouldReceive('handle')
            ->never();

        static::assertFalse($this->handler->handle($this->request, $this->configuration));
    }

    public function testInterruptOnNoApply(): void
    {
        $this->converter
            ->shouldReceive('supports')
            ->once()
            ->with($this->configuration)
            ->andReturnTrue();

        $this->converter
            ->shouldReceive('apply')
            ->once()
            ->with($this->request, $this->configuration)
            ->andReturnFalse();

        $this->next
            ->shouldReceive('handle')
            ->never();

        static::assertFalse($this->handler->handle($this->request, $this->configuration));
    }
}
