<?php

namespace Lores\RestParamConverterBundle\Tests\Component\ParamConverter\ChainedHandler\Helper;

final class TestObject
{
    private $propertyA;

    private $propertyB;

    /**
     * @param $propertyA
     * @param $propertyB
     */
    public function __construct($propertyA, $propertyB)
    {
        $this->propertyA = $propertyA;
        $this->propertyB = $propertyB;
    }

    /**
     * @return mixed
     */
    public function getPropertyA()
    {
        return $this->propertyA;
    }

    /**
     * @return mixed
     */
    public function getPropertyB()
    {
        return $this->propertyB;
    }
}
