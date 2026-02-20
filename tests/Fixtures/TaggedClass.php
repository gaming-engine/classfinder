<?php

declare(strict_types=1);

namespace GamingEngine\ClassFinder\Tests\Fixtures;

use Attribute;

#[Attribute]
class SampleAttribute {}

#[SampleAttribute]
class TaggedClass
{
    public function value(): string
    {
        return 'tagged';
    }
}
