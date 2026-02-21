# ClassFinder

Runtime class discovery for PHP using Composer's PSR-4 autoloader. Find classes that implement an interface, extend a base class, or use a specific attribute — without maintaining manifests or config files.

## Requirements

- PHP 8.4+
- Composer (PSR-4 autoloading)

## Installation

```bash
composer require gamingengine/classfinder
```

## Usage

### Find classes that implement an interface

```php
use GamingEngine\ClassFinder\ClassFinder;

$implementations = ClassFinder::interfaces(MyInterface::class);
// ['App\Services\ConcreteA', 'App\Services\ConcreteB']
```

### Find subclasses of a base class

```php
$subclasses = ClassFinder::classes(BaseHandler::class);
// ['App\Handlers\EmailHandler', 'App\Handlers\SmsHandler']
```

### Find classes with a specific attribute

```php
$tagged = ClassFinder::withAttribute(AsCommand::class);
// ['App\Commands\ImportData', 'App\Commands\ExportData']
```

### Namespace filtering

All discovery methods accept a namespace parameter to limit the search scope. This defaults to `GamingEngine\\` but can be set to any namespace registered in Composer's PSR-4 autoloader.

```php
// Search only within a specific namespace
$modules = ClassFinder::interfaces(ModuleContract::class, 'App\\Modules\\');

// Search across all registered namespaces
$all = ClassFinder::interfaces(ModuleContract::class, '');
```

### Caching

Results are cached in memory for the lifetime of the request using an `ArrayMemoizer`. You can flush the cache or swap in your own implementation:

```php
use GamingEngine\ClassFinder\ClassFinder;
use GamingEngine\ClassFinder\Contracts\MemoizerContract;

// Flush the cache
ClassFinder::flush();

// Use a custom memoizer (e.g. Redis, filesystem)
ClassFinder::setMemoizer(new class implements MemoizerContract {
    public function remember(string $key, callable $callback): mixed
    {
        return Cache::remember($key, 3600, $callback);
    }

    public function flush(): void
    {
        Cache::flush();
    }
});
```

## How it works

ClassFinder reads Composer's registered PSR-4 prefixes via `ClassLoader::getPrefixesPsr4()` and recursively scans the corresponding directories for PHP files. It parses each file to extract the fully qualified class name, then uses reflection to check whether it matches the given criteria.

Classes under `\Tests\` namespaces are automatically excluded.

## License

MIT
