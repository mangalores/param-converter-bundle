<?php

namespace Lores\RestParamConverterBundle\Tests\Component\ParamConverter;

use Lores\RestParamConverterBundle\Component\ParamConverter\ByMethodParamConverter;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandlerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Lores\RestParamConverterBundle\Component\ParamConverter\ByMethodParamConverter
 */
final class ByMethodParamConverterTest extends MockeryTestCase
{
    private const METHOD = 'Foo';

    /**
     * @var ByMethodParamConverter
     */
    private $converter;

    /**
     * @var ChainedHandlerInterface|MockInterface
     */
    private $handler;

    /**
     * @var MockInterface|Request
     */
    private $request;

    /**
     * @var ParamConverter|Mockery\MockInterface
     */
    private $configuration;

    protected function setup(): void
    {
        $this->request = Mockery::mock(Request::class);
        $this->configuration = Mockery::mock(ParamConverter::class);
        $this->handler = Mockery::mock(ChainedHandlerInterface::class);
        $this->converter = new ByMethodParamConverter(self::METHOD, $this->handler);

        parent::setUp();
    }

    /**
     * @dataProvider provideHandlerResult
     */
    public function testApply(bool $expected): void
    {
        $this->request
            ->shouldReceive('isMethod')
            ->once()
            ->with(self::METHOD)
            ->andReturn($expected);

        if ($expected) {
            $this->handler
                ->shouldReceive('handle')
                ->with($this->request, $this->configuration)
                ->once()
                ->andReturn($expected);
        }

        static::assertEquals($expected, $this->converter->apply($this->request, $this->configuration));
    }

    public function provideHandlerResult(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider provideClassValues
     */
    public function testConverterSupport(bool $expected, ?string $class): void
    {
        $this->configuration
            ->shouldReceive('getClass')
            ->once()
            ->andReturn($class);

        static::assertEquals($expected, $this->converter->supports($this->configuration));
    }

    public function provideClassValues(): array
    {
        return [
            [true, \stdClass::class],
            [false, 'Foo'],
            [false, ''],
            [false, null],
        ];
    }
}
