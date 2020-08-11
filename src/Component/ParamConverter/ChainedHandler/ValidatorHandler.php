<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler;

use JMS\Serializer\Exception\ValidationFailedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidatorHandler extends ChainedHandler
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();

        if ($request->attributes->has($name)) {
            $errors = $this->validator->validate($request->attributes->get($name));

            if (0 !== $errors->count()) {
                throw new ValidationFailedException($errors);
            }
        }

        return parent::handle($request, $configuration);
    }
}
