<?php

declare(strict_types=1);

namespace GamingEngine\ClassFinder\Contracts;

interface MemoizerContract
{
    public function remember(string $key, callable $callback): mixed;

    public function flush(): void;
}
