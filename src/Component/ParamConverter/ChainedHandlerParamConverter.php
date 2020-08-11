<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter;

use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ChainedHandlerParamConverter implements ParamConverterInterface
{
    /**
     * @var ChainedHandlerInterface
     */
    private $handler;

    public function __construct(ChainedHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        return $this->handler->handle($request, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return \class_exists($configuration->getClass());
    }
}
