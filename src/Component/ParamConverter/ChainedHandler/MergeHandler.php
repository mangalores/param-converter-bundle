<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler;

use LogicException;
use ReflectionClass;
use ReflectionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

final class MergeHandler extends ChainedHandler
{
    /**
     * @var ChainedHandlerInterface
     */
    private $handler;

    /**
     * @var bool
     */
    private $partial = false;

    public function __construct(ChainedHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function setPartial(bool $partial): void
    {
        $this->partial = $partial;
    }

    public function handle(Request $request, ParamConverter $configuration): bool
    {
        $reflection = $this->makeReflection($configuration->getClass());

        if (!$reflection instanceof ReflectionClass) {
            return false;
        }

        $name = $configuration->getName();
        $objectA = $request->attributes->get($name);
        $request->attributes->remove($name);
        $this->handler->handle($request, $configuration);
        $objectB = $request->attributes->get($name);
        $request->attributes->set($name, $objectA);


        $this->merge($reflection, $objectA, $objectB);

        return parent::handle($request, $configuration);
    }

    private function makeReflection(string $class): ?ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (ReflectionException $e) {
            return null;
        }
    }

    private function merge(ReflectionClass $reflection, $objectA, $objectB): void
    {
        if (
            !(\is_object($objectA) && $reflection->isInstance($objectA))
            ||
            !(\is_object($objectB) &&$reflection->isInstance($objectB))) {

            throw new LogicException(
                \sprintf('on merge both objects (a and b) must be instances of class %s', $reflection->getShortName())
            );
        }

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            if ($this->partial && null === $property->getValue($objectB)) {
                continue;
            }

            $property->setValue($objectA, $property->getValue($objectB));
        }
    }

}
