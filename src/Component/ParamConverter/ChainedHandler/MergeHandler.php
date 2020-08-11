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
        $class = $configuration->getClass();

        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            return false;
        }

        $name = $configuration->getName();
        $current = $request->attributes->get($name);
        $request->attributes->remove($name);
        $this->handler->handle($request, $configuration);
        $new = $request->attributes->get($name);

        if (!(\is_a($current, $class) && \is_a($new, $class))) {
            throw new LogicException('on merge both objects (a and b) must be of expected class instance');
        }

        $this->merge($reflection, $current, $new);

        return parent::handle($request, $configuration);
    }

    private function merge(ReflectionClass $reflection, object $current, object $new): void
    {
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            if ($this->partial && null === $property->getValue($new)) {
                continue;
            }

            $property->setValue($current, $property->getValue($new));
        }
    }
}
