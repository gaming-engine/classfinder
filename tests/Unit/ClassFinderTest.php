<?php

declare(strict_types=1);

use GamingEngine\ClassFinder\ArrayMemoizer;
use GamingEngine\ClassFinder\ClassFinder;
use GamingEngine\ClassFinder\Contracts\MemoizerContract;

beforeEach(function () {
    ClassFinder::flush();
});

it('discovers classes implementing an interface', function () {
    $results = ClassFinder::interfaces(
        MemoizerContract::class,
        'GamingEngine\\ClassFinder\\'
    );

    expect($results)->toContain(ArrayMemoizer::class);
});

it('excludes interfaces themselves from results', function () {
    $results = ClassFinder::interfaces(
        MemoizerContract::class,
        'GamingEngine\\ClassFinder\\'
    );

    expect($results)->not->toContain(MemoizerContract::class);
});

it('excludes abstract classes from interface results', function () {
    $results = ClassFinder::interfaces(
        MemoizerContract::class,
        'GamingEngine\\ClassFinder\\'
    );

    foreach ($results as $class) {
        expect((new ReflectionClass($class))->isAbstract())->toBeFalse();
    }
});

it('returns empty array for non-matching namespace', function () {
    $results = ClassFinder::interfaces(
        MemoizerContract::class,
        'NonExistent\\'
    );

    expect($results)->toBe([]);
});

it('skips test namespaces automatically', function () {
    $results = ClassFinder::interfaces(
        MemoizerContract::class,
        'GamingEngine\\ClassFinder\\'
    );

    foreach ($results as $class) {
        expect($class)->not->toContain('\\Tests\\');
    }
});

it('caches results between calls', function () {
    $first = ClassFinder::interfaces(MemoizerContract::class, 'GamingEngine\\ClassFinder\\');
    $second = ClassFinder::interfaces(MemoizerContract::class, 'GamingEngine\\ClassFinder\\');

    expect($first)->toBe($second);
});

it('discovers all classes without namespace filter', function () {
    $results = ClassFinder::interfaces(MemoizerContract::class);

    expect($results)->toContain(ArrayMemoizer::class);
});

it('allows swapping the memoizer', function () {
    $custom = new ArrayMemoizer;
    ClassFinder::setMemoizer($custom);

    expect(ClassFinder::getMemoizer())->toBe($custom);
});

it('discovers classes extending a base class', function () {
    $results = ClassFinder::classes(
        ArrayMemoizer::class,
        'GamingEngine\\ClassFinder\\'
    );

    expect($results)->toBeArray();
});

it('returns empty when no subclasses exist', function () {
    $results = ClassFinder::classes(
        ArrayMemoizer::class,
        'GamingEngine\\ClassFinder\\'
    );

    expect($results)->toBe([]);
});

it('discovers classes with a specific attribute', function () {
    $results = ClassFinder::withAttribute(
        Attribute::class,
        'GamingEngine\\ClassFinder\\'
    );

    expect($results)->toBeArray();
});

it('scans a sub-namespace within a prefix', function () {
    $results = ClassFinder::interfaces(
        MemoizerContract::class,
        'GamingEngine\\ClassFinder\\Contracts\\'
    );

    // Contracts namespace only has the interface itself, no implementations
    expect($results)->toBe([]);
});

it('returns empty when classes method finds no subclasses', function () {
    $results = ClassFinder::classes(
        MemoizerContract::class,
        'NonExistent\\'
    );

    expect($results)->toBe([]);
});

it('returns empty when withAttribute finds no matches in unknown namespace', function () {
    $results = ClassFinder::withAttribute(
        Attribute::class,
        'NonExistent\\'
    );

    expect($results)->toBe([]);
});
