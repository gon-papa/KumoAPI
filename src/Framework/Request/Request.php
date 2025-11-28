<?php

declare(strict_types=1);

namespace Framework\Request;

class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $headers,
        public readonly array $body,
        public readonly ?array $json,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $h = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$h] = $value;
            }
        }

        $raw = file_get_contents('php://input') ?: '';
        $json = null;
        if (isset($headers['content-type']) && str_contains($headers['content-type'], 'application/json')) {
            $json = json_decode($raw, true);
        }

        return new self($method, $path, $_GET, $headers, $_POST, $json);
    }
}
