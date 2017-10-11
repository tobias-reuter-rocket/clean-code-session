<?php

// Liskov substitution principle

// Objects in a program should be replaceable with instances of their subtypes without altering
// the correctness of that program
// Class B extending class A cannot change its behavior

class Rectangle
{
    /** @var int */
    protected $width;

    /** @var int */
    protected $height;

    public function setWidth(int $width)
    {
        $this->width = $width;
    }

    public function setHeight(int $height)
    {
        $this->height = $height;
    }

    public function getArea()
    {
        return $this->width * $this->height;
    }
}

class Square extends Rectangle
{
    public function setWidth(int $width)
    {
        $this->width = $width;
        $this->height = $width;
    }

    public function setHeight(int $height)
    {
        $this->height = $height;
        $this->width = $height;
    }
}

function foo(Rectangle $rectangle, int $width, int $height) {
    $rectangle->setWidth($width);    // 7
    $rectangle->setHeight($height);  // 3
    echo $rectangle->getArea();
}

interface Resource
{
    public function load();
    public function persist();
}

class Settings implements Resource
{
    public function load()
    {
        // TODO: Implement load() method.
    }

    public function persist()
    {
        // TODO: Implement persist() method.
    }
}

class UserConfig implements Resource
{
    public function load()
    {
        // TODO: Implement load() method.
    }

    public function persist()
    {
        // TODO: Implement persist() method.
    }
}

class ReadOnlyResource implements Resource
{
    public function load()
    {
        // TODO: Implement load() method.
    }

    public function persist()
    {
        throw new NotImplementedException;
    }
}

$resources = [
    new Settings(),
    new UserConfig(),
    new ReadOnlyResource(),
];

foreach ($resources as $resource) {
    $resource->load();
}

// some stuff happens...

foreach ($resources as $resource) {
    $resource->persist();
}
