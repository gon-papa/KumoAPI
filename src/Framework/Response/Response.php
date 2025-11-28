<?php

declare(strict_types=1);

namespace Framework\Response;

class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private string $content,
        private int $status = 200,
        private array $headers = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function json(array $data, int $status = 200): self
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        return new self($content === false ? '' : $content, $status, [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header("$k: $v");
        }
        echo $this->content;
    }
}
