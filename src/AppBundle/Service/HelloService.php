<?php

namespace AppBundle\Service;


class HelloService
{
    /**
     * @var string
     */
    private $name;

    /**
     * HelloService constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return HelloService
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    public function sayHello() {
        return 'hello ' . $this->name;
    }
}