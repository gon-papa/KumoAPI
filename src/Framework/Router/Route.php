<?php

declare(strict_types=1);

namespace Framework\Router;

class Route
{
    private static ?Router $router = null;
    /** @var list<string> */
    private static array $prefixStack = [''];

    public static function setRouter(Router $router): void
    {
        self::$router = $router;
    }

    public static function route(string $prefix, callable $callback): void
    {
        self::ensureRouter();

        $normalizedPrefix = self::normalizePrefix($prefix);
        $current = end(self::$prefixStack) ?: '';
        self::$prefixStack[] = $current . $normalizedPrefix;

        try {
            $callback();
        } finally {
            array_pop(self::$prefixStack);
        }
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public static function get(string $path, array $handler): void
    {
        self::register('GET', $path, $handler);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    public static function post(string $path, array $handler): void
    {
        self::register('POST', $path, $handler);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     */
    private static function register(string $method, string $path, array $handler): void
    {
        $router = self::ensureRouter();

        $router->add($method, self::buildPath($path), $handler);
    }

    private static function buildPath(string $path): string
    {
        $base = end(self::$prefixStack) ?: '';
        $segment = trim($path, '/');

        $full = $base;
        if ($segment !== '') {
            $full .= '/' . $segment;
        }

        // Ensure a single leading slash and no trailing slash (except root)
        $full = '/' . ltrim($full, '/');
        $full = rtrim($full, '/') ?: '/';

        return $full;
    }

    private static function normalizePrefix(string $prefix): string
    {
        $clean = '/' . trim($prefix, '/');
        return $clean === '/' ? '' : $clean;
    }

    private static function ensureRouter(): Router
    {
        if (self::$router === null) {
            throw new RouterException('Router instance is not set. Call Route::setRouter() first.');
        }

        return self::$router;
    }
}
