<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

interface ChainedHandlerInterface
{
    public function handle(Request $request, ParamConverter $configuration): bool;

    public function setNext(?ChainedHandlerInterface $next = null): void;
}
