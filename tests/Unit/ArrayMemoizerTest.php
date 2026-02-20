<?php

declare(strict_types=1);

use GamingEngine\ClassFinder\ArrayMemoizer;

beforeEach(function () {
    $this->memoizer = new ArrayMemoizer;
    $this->memoizer->flush();
});

it('stores and retrieves a value', function () {
    $result = $this->memoizer->remember('key', fn () => 'value');

    expect($result)->toBe('value');
});

it('returns cached value on subsequent calls', function () {
    $counter = 0;

    $this->memoizer->remember('key', function () use (&$counter) {
        $counter++;

        return 'value';
    });

    $this->memoizer->remember('key', function () use (&$counter) {
        $counter++;

        return 'other';
    });

    expect($counter)->toBe(1);
});

it('caches different keys independently', function () {
    $this->memoizer->remember('a', fn () => 'alpha');
    $this->memoizer->remember('b', fn () => 'beta');

    expect($this->memoizer->remember('a', fn () => 'wrong'))->toBe('alpha')
        ->and($this->memoizer->remember('b', fn () => 'wrong'))->toBe('beta');
});

it('clears cache on flush', function () {
    $this->memoizer->remember('key', fn () => 'old');
    $this->memoizer->flush();
    $result = $this->memoizer->remember('key', fn () => 'new');

    expect($result)->toBe('new');
});
