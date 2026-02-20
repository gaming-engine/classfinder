<?php

declare(strict_types=1);

namespace GamingEngine\ClassFinder;

use GamingEngine\ClassFinder\Contracts\MemoizerContract;

class ArrayMemoizer implements MemoizerContract
{
    /** @var array<string, mixed> */
    private static array $cache = [];

    public function remember(string $key, callable $callback): mixed
    {
        if (! array_key_exists($key, self::$cache)) {
            self::$cache[$key] = $callback();
        }

        return self::$cache[$key];
    }

    public function flush(): void
    {
        self::$cache = [];
    }
}
