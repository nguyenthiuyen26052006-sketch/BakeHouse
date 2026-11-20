<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** @var (callable(callable(string, array): void): void)|null */
    private $stream = null;

    public function __construct(
        private string $content = '',
        private int $statusCode = 200,
        private array $headers = []
    ) {
    }

    public static function html(string $content, int $statusCode = 200): self
    {
        return new self($content, $statusCode, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function json(array $data, int $statusCode = 200): self
    {
        return new self(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $statusCode,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    /**
     * Server-Sent Events response. The producer receives an emit(event, data) callable; every call is flushed
     * to the client immediately. Emitting after the client disconnected throws StreamAborted.
     *
     * @param callable(callable(string, array): void): void $producer
     */
    public static function stream(callable $producer): self
    {
        $response = new self('', 200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
        $response->stream = $producer;

        return $response;
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($this->stream !== null) {
            $this->sendStream($this->stream);

            return;
        }

        echo $this->content;
    }

    private function sendStream(callable $producer): void
    {
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        @set_time_limit(120);
        ignore_user_abort(false);

        // Comment padding so proxies/browsers that buffer the first bytes start delivering immediately.
        echo ':' . str_repeat(' ', 2048) . "\n\n";
        flush();

        $emit = static function (string $event, array $data): void {
            echo 'event: ' . $event . "\n" . 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";
            flush();
            if (connection_aborted()) {
                throw new StreamAborted('Client disconnected.');
            }
        };

        try {
            $producer($emit);
        } catch (StreamAborted) {
            // client left; nothing more to send
        }
    }
}
