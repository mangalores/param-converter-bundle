<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

final class DelegateHandler extends ChainedHandler
{
    /**
     * @var ParamConverterInterface
     */
    private $converter;

    public function __construct(ParamConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, ParamConverter $configuration): bool
    {
        if (!$this->converter->supports($configuration)) {
            return false;
        }

        if (!$this->converter->apply($request, $configuration)) {
            return false;
        }

        return parent::handle($request, $configuration);
    }
}
