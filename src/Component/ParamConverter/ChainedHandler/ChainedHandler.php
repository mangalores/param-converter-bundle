<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

abstract class ChainedHandler implements ChainedHandlerInterface
{
    /**
     * @var ChainedHandlerInterface|null
     */
    private $next;

    public function setNext(?ChainedHandlerInterface $next = null): void
    {
        $this->next = $next;
    }

    public function handle(Request $request, ParamConverter $configuration): bool
    {
        if (null === $this->next) {
            return true;
        }

        return $this->next->handle($request, $configuration);
    }
}
