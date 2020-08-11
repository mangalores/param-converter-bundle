<?php

namespace Lores\RestParamConverterBundle\Component\ParamConverter\ChainedHandler;

use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DeserializeHandler extends ChainedHandler
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var DeserializationContextFactoryInterface
     */
    private $factory;

    public function __construct(SerializerInterface $serializer, DeserializationContextFactoryInterface $factory)
    {
        $this->serializer = $serializer;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, ParamConverter $configuration): bool
    {
        try {
            $entity = $this->serializer->deserialize(
                (string) $request->getContent(),
                $configuration->getClass(),
                'json',
                $this->factory->createDeserializationContext()
            );
            $request->attributes->set($configuration->getName(), $entity);
        } catch (\Throwable $e) {
            if (!$configuration->isOptional()) {
                throw new BadRequestHttpException($e->getMessage());
            }

            $request->attributes->set($configuration->getName(), null);
        }

        return parent::handle($request, $configuration);
    }
}
