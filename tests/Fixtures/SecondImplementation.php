<?php

declare(strict_types=1);

namespace GamingEngine\ClassFinder\Tests\Fixtures;

class SecondImplementation implements DiscoverableInterface
{
    public function identify(): string
    {
        return 'second';
    }
}
