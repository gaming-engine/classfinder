<?php

declare(strict_types=1);

namespace GamingEngine\ClassFinder\Tests\Fixtures;

class ConcreteImplementation implements DiscoverableInterface
{
    public function identify(): string
    {
        return 'concrete';
    }
}
