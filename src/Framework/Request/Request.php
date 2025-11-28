<?php

declare(strict_types=1);

namespace Framework\Request;

class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     * @param array<string, mixed> $body
     * @param array<string, mixed>|null $json
    */
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
        $method = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $parsedPath = parse_url($uri, PHP_URL_PATH);
        $path = is_string($parsedPath) && $parsedPath !== '' ? $parsedPath : '/';

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $h = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$h] = (string)$value;
            }
        }

        $raw = file_get_contents('php://input') ?: '';
        $json = null;
        $contentType = $headers['content-type'] ?? '';
        if ($contentType !== '' && str_contains($contentType, 'application/json')) {
            $decoded = json_decode($raw, true);
            $json = is_array($decoded) ? $decoded : null;
        }

        return new self($method, $path, $_GET, $headers, $_POST, $json);
    }
}
