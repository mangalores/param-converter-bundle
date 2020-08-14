<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter;

use Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler\ChainedHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

final class ByMethodParamConverter extends ChainedParamConverter
{
    /**
     * @var string
     */
    private $method;

    public function __construct(string $method, ChainedHandlerInterface $handler)
    {
        $this->method = $method;

        parent::__construct($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        if (!$request->isMethod($this->method)) {
            return false;
        }

        return  parent::apply($request, $configuration);
    }
}
