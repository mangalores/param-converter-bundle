<?php

namespace Lores\RestParamConverterBundle\Tests\Component\ParamConverter\ChainedHandler;

use LogicException;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandlerInterface;
use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\MergeHandler;
use Lores\RestParamConverterBundle\Tests\Component\ParamConverter\ChainedHandler\Helper\TestObject;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use stdClass;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\MergeHandler
 */
final class MergeHandlerTest extends MockeryTestCase
{
    private const VALID_CLASS_NAME = TestObject::class;
    private const INVALID_CLASS_NAME = 'Foo';
    private const ATTRIBUTE = 'foo';

    /**
     * @var MergeHandler
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
     * @var Mockery\MockInterface|ChainedHandlerInterface
     */
    private $delegatedHandler;

    protected function setUp(): void
    {
        $this->request = Mockery::mock(Request::class);
        $this->configuration = Mockery::mock(ParamConverter::class);
        $this->delegatedHandler = Mockery::mock(ChainedHandlerInterface::class);
        $this->handler = new MergeHandler($this->delegatedHandler);

        parent::setUp();
    }

    /**
     * @dataProvider provideTestProperties
     */
    public function testMergeObjects($objectA, $objectB, $expectedA, $expectedB): void
    {
        $this->setUpValidTestCase();
        $this->setUpParamBag($objectA, $objectB);

        static::assertTrue($this->handler->handle($this->request, $this->configuration));

        static::assertSame($expectedA, $objectA->getPropertyA());
        static::assertSame($expectedB, $objectA->getPropertyB());
    }

    public function provideTestProperties(): array
    {
        return [
            [
                new TestObject('foo', 'bar'),
                new TestObject('baz', 'burb'),
                'baz',
                'burb',
            ],
            [
                new TestObject('foo', 'bar'),
                new TestObject('baz', null),
                'baz',
                null,
            ],

            [
                new TestObject('foo', 'bar'),
                new TestObject(null, 'burb'),
                null,
                'burb',
            ],
        ];
    }

    /**
     * @dataProvider providePartialTestProperties
     */
    public function testPartialMergeObjects($objectA, $objectB, $expectedA, $expectedB): void
    {
        $this->setUpValidTestCase();
        $this->setUpParamBag($objectA, $objectB);

        $this->handler->setPartial(true);

        static::assertTrue($this->handler->handle($this->request, $this->configuration));
        static::assertSame($expectedA, $objectA->getPropertyA());
        static::assertSame($expectedB, $objectA->getPropertyB());
    }

    public function providePartialTestProperties(): array
    {
        return [
            [
                new TestObject('foo', 'bar'),
                new TestObject('baz', 'burb'),
                'baz',
                'burb',
            ],
            [
                new TestObject('foo', 'bar'),
                new TestObject('baz', null),
                'baz',
                'bar',
            ],

            [
                new TestObject('foo', 'bar'),
                new TestObject(null, 'burb'),
                'foo',
                'burb',
            ],
        ];
    }

    public function testReflectionError(): void
    {
        $this->configuration
            ->shouldReceive('getClass')
            ->once()
            ->andReturn(self::INVALID_CLASS_NAME);

        static::assertFalse($this->handler->handle($this->request, $this->configuration));
    }

    /**
     * @dataProvider provideInvalidObjects
     */
    public function testInvalidObjects($currentObject, $newObject): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('on merge both objects (a and b) must be of expected class instance');

        $this->configuration
            ->shouldReceive('getName')
            ->andReturn(self::ATTRIBUTE);

        $this->configuration
            ->shouldReceive('getClass')
            ->andReturn(TestObject::class);

        $this->setUpParamBag($currentObject, $newObject);

        $this->handler->handle($this->request, $this->configuration);
    }

    public function provideInvalidObjects(): array
    {
        return [
            [
                'foo',
                'bar',
            ],

            [
                TestObject::class,
                true,
            ],
            [
                true,
                TestObject::class,
            ],
            [
                TestObject::class,
                null,
            ],
            [
                null,
                TestObject::class,
            ],
            [
                TestObject::class,
                123,
            ],
            [
                123,
                TestObject::class,
            ],
            [
                'foo',
                TestObject::class,
            ],
            [
                TestObject::class,
                'bar',
            ],
            [
                stdClass::class,
                TestObject::class,
            ],
            [
                TestObject::class,
                stdClass::class,
            ],
        ];
    }

    private function setUpValidTestCase(): void
    {
        $this->configuration
            ->shouldReceive('getClass')
            ->once()
            ->andReturn(self::VALID_CLASS_NAME);

        $this->configuration
            ->shouldReceive('getName')
            ->once()
            ->andReturn(self::ATTRIBUTE);
    }

    private function setUpParamBag($currentObject, $newObject): void
    {
        $this->request->attributes = Mockery::mock(ParameterBag::class);
        $this->request->attributes
            ->shouldReceive('get')
            ->with(self::ATTRIBUTE)
            ->twice()
            ->andReturn($currentObject, $newObject);

        $this->request->attributes
            ->shouldReceive('remove')
            ->with(self::ATTRIBUTE)
            ->once();

        $this->request->attributes
            ->shouldReceive('set')
            ->with(self::ATTRIBUTE, $newObject)
            ->once();

        $this->delegatedHandler
            ->shouldReceive('handle')
            ->with($this->request, $this->configuration)
            ->andReturnUsing(
                static function (Request $request, ParamConverter $configuration) use ($newObject) {
                    $request
                        ->attributes->set(self::ATTRIBUTE, $newObject);

                    return true;
                }
            );
    }
}
