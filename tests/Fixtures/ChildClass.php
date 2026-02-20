<?php

declare(strict_types=1);

namespace GamingEngine\ClassFinder\Tests\Fixtures;

class ChildClass extends BaseClass
{
    public function name(): string
    {
        return 'child';
    }
}
