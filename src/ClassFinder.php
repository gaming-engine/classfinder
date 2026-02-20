<?php

declare(strict_types=1);

namespace GamingEngine\ClassFinder;

use Composer\Autoload\ClassLoader;
use GamingEngine\ClassFinder\Contracts\MemoizerContract;
use ReflectionClass;

class ClassFinder
{
    private static ?MemoizerContract $memoizer = null;

    public static function setMemoizer(MemoizerContract $memoizer): void
    {
        self::$memoizer = $memoizer;
    }

    public static function getMemoizer(): MemoizerContract
    {
        return self::$memoizer ??= new ArrayMemoizer;
    }

    public static function flush(): void
    {
        self::getMemoizer()->flush();
    }

    /**
     * @return list<class-string>
     */
    public static function interfaces(string $interface, string $namespace = 'GamingEngine\\'): array
    {
        $key = "interfaces:{$interface}:{$namespace}";

        return self::getMemoizer()->remember($key, function () use ($interface, $namespace): array {
            return array_values(array_filter(
                self::allClasses($namespace),
                static function (string $class) use ($interface): bool {
                    try {
                        $reflection = new ReflectionClass($class);

                        return $reflection->implementsInterface($interface)
                            && ! $reflection->isAbstract()
                            && ! $reflection->isInterface();
                    } catch (\Throwable) {
                        return false;
                    }
                },
            ));
        });
    }

    /**
     * @return list<class-string>
     */
    public static function classes(string $baseClass, string $namespace = 'GamingEngine\\'): array
    {
        $key = "classes:{$baseClass}:{$namespace}";

        return self::getMemoizer()->remember($key, function () use ($baseClass, $namespace): array {
            return array_values(array_filter(
                self::allClasses($namespace),
                static function (string $class) use ($baseClass): bool {
                    try {
                        $reflection = new ReflectionClass($class);

                        return $reflection->isSubclassOf($baseClass)
                            && ! $reflection->isAbstract();
                    } catch (\Throwable) {
                        return false;
                    }
                },
            ));
        });
    }

    /**
     * @return list<class-string>
     */
    public static function withAttribute(string $attribute, string $namespace = 'GamingEngine\\'): array
    {
        $key = "attribute:{$attribute}:{$namespace}";

        return self::getMemoizer()->remember($key, function () use ($attribute, $namespace): array {
            return array_values(array_filter(
                self::allClasses($namespace),
                static function (string $class) use ($attribute): bool {
                    try {
                        $reflection = new ReflectionClass($class);

                        return $reflection->getAttributes($attribute) !== [];
                    } catch (\Throwable) {
                        return false;
                    }
                },
            ));
        });
    }

    /**
     * @return list<class-string>
     */
    private static function allClasses(string $namespace): array
    {
        $key = 'all_classes:'.$namespace;

        return self::getMemoizer()->remember($key, function () use ($namespace): array {
            $loader = self::getComposerClassLoader();

            if ($loader === null) {
                return [];
            }

            $classes = [];

            foreach ($loader->getPrefixesPsr4() as $prefix => $directories) {
                if (! str_starts_with($prefix, $namespace)
                    && ! str_starts_with($namespace, $prefix)) {
                    continue;
                }

                $subPath = '';
                if (strlen($namespace) > strlen($prefix)) {
                    $subPath = str_replace('\\', '/', substr($namespace, strlen($prefix)));
                }

                foreach ((array) $directories as $directory) {
                    $fullPath = rtrim($directory, '/');
                    if ($subPath !== '') {
                        $fullPath .= '/'.ltrim($subPath, '/');
                    }

                    if (is_dir($fullPath)) {
                        $classes = array_merge($classes, self::findClassesInDirectory($fullPath, $namespace));
                    }
                }
            }

            return $classes;
        });
    }

    /**
     * @return list<class-string>
     */
    private static function findClassesInDirectory(string $dir, string $namespace): array
    {
        $classes = [];

        $files = glob($dir.'/*.php');
        if ($files !== false) {
            foreach ($files as $file) {
                $className = self::extractClassName($file);
                if ($className === null) {
                    continue;
                }

                if (str_contains($className, '\\Tests\\') || str_starts_with($className, 'Tests\\')) {
                    continue;
                }

                if (! str_starts_with($className, $namespace)) {
                    continue;
                }

                try {
                    if (class_exists($className) || interface_exists($className)) {
                        $classes[] = $className;
                    }
                } catch (\Throwable) {
                }
            }
        }

        $dirs = glob($dir.'/*', GLOB_ONLYDIR);
        if ($dirs !== false) {
            foreach ($dirs as $subDir) {
                $classes = array_merge(
                    $classes,
                    self::findClassesInDirectory($subDir, $namespace)
                );
            }
        }

        return $classes;
    }

    private static function extractClassName(string $file): ?string
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $namespace = '';
        if (preg_match('/^\s*namespace\s+(.+?)\s*;/m', $content, $matches)) {
            $namespace = $matches[1].'\\';
        }

        if (preg_match('/^\s*(?:(?:abstract|final|readonly)\s+)*(?:class|interface|trait|enum)\s+(\w+)/m', $content, $matches)) {
            return $namespace.$matches[1];
        }

        return null;
    }

    private static function getComposerClassLoader(): ?ClassLoader
    {
        foreach (spl_autoload_functions() as $autoloader) {
            if (is_array($autoloader) && $autoloader[0] instanceof ClassLoader) {
                return $autoloader[0];
            }
        }

        return null;
    }
}
