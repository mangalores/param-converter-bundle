<?php

namespace Lores\RestParamConverterBundle\Tests\Component\ParamConverter;

use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandlerInterface;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandlerParamConverter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandlerParamConverter
 */
final class ChainedHandlerParamConverterTest extends MockeryTestCase
{
    /**
     * @var ChainedHandlerParamConverter
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
    private $handler;

    protected function setUp(): void
    {
        $this->request = Mockery::mock(Request::class);
        $this->configuration = Mockery::mock(ParamConverter::class);
        $this->handler = Mockery::mock(ChainedHandlerInterface::class);
        $this->converter = new ChainedHandlerParamConverter($this->handler);

        parent::setUp();
    }

    /**
     * @dataProvider provideSupportCases
     */
    public function testSupports(string $class, bool $expect): void
    {
        $this->configuration
            ->shouldReceive('getClass')
            ->once()
            ->andReturn($class);

        static::assertSame($expect, $this->converter->supports($this->configuration));
    }

    public function provideSupportCases(): array
    {
        return [
            [\stdClass::class, true],
            ['Foo', false],
        ];
    }

    public function testHandle(): void
    {
        $this->handler
            ->shouldReceive('handle')
            ->with($this->request, $this->configuration)
            ->once()
            ->andReturnTrue();

        static::assertTrue($this->converter->apply($this->request, $this->configuration));
    }
}
