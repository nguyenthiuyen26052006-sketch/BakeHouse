<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Core\Env;
use RuntimeException;

/** Minimal Azure OpenAI (v1 API) client over cURL: chat completions with tools + embeddings. */
final class AzureOpenAiClient
{
    private const TIMEOUT = 60;

    /**
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @param (callable(string): void)|null $onDelta when given, the reply is streamed and every text token is passed to it
     * @return array<string, mixed> the assistant message (content / tool_calls)
     */
    public function chat(array $messages, array $tools = [], ?callable $onDelta = null): array
    {
        $payload = [
            'model' => $this->required('AZURE_OPENAI_DEPLOYMENT'),
            'messages' => $messages,
            'max_completion_tokens' => 4000,
            'reasoning_effort' => 'minimal',
        ];
        if ($tools !== []) {
            $payload['tools'] = $tools;
        }

        if ($onDelta !== null) {
            return $this->streamChat($payload, $onDelta);
        }

        $response = $this->request('/chat/completions', $payload);
        $message = $response['choices'][0]['message'] ?? null;
        if (!is_array($message)) {
            throw new RuntimeException('Phản hồi Azure OpenAI không hợp lệ.');
        }

        return $message;
    }

    /**
     * @param array<int, string> $texts
     * @return array<int, array<int, float>> one vector per input, same order
     */
    public function embed(array $texts): array
    {
        if ($texts === []) {
            return [];
        }
        $response = $this->request('/embeddings', [
            'model' => $this->required('AZURE_OPENAI_EMBEDDING_DEPLOYMENT'),
            'input' => array_values($texts),
        ]);

        $vectors = [];
        foreach ($response['data'] ?? [] as $item) {
            $vectors[(int) $item['index']] = array_map('floatval', $item['embedding']);
        }
        ksort($vectors);
        if (count($vectors) !== count($texts)) {
            throw new RuntimeException('Azure OpenAI trả về thiếu embedding.');
        }

        return array_values($vectors);
    }

    /**
     * Streaming chat completion: forwards text tokens to $onDelta and reassembles tool calls from their fragments.
     *
     * @param array<string, mixed> $payload
     * @param callable(string): void $onDelta
     * @return array<string, mixed>
     */
    private function streamChat(array $payload, callable $onDelta): array
    {
        $payload['stream'] = true;
        $endpoint = rtrim($this->required('AZURE_OPENAI_ENDPOINT'), '/');
        $apiKey = $this->required('AZURE_OPENAI_API_KEY');

        $buffer = '';
        $raw = '';
        $content = '';
        $calls = [];
        $failure = null;

        $handleLine = static function (string $line) use (&$content, &$calls, $onDelta): void {
            if (!str_starts_with($line, 'data:')) {
                return;
            }
            $data = trim(substr($line, 5));
            if ($data === '' || $data === '[DONE]') {
                return;
            }
            $json = json_decode($data, true);
            $delta = is_array($json) ? ($json['choices'][0]['delta'] ?? null) : null;
            if (!is_array($delta)) {
                return; // keep-alive / content-filter chunk without choices
            }
            if (isset($delta['content']) && is_string($delta['content']) && $delta['content'] !== '') {
                $content .= $delta['content'];
                $onDelta($delta['content']);
            }
            foreach ($delta['tool_calls'] ?? [] as $fragment) {
                $i = (int) ($fragment['index'] ?? 0);
                $calls[$i] ??= ['id' => '', 'type' => 'function', 'function' => ['name' => '', 'arguments' => '']];
                if (!empty($fragment['id'])) {
                    $calls[$i]['id'] = (string) $fragment['id'];
                }
                $calls[$i]['function']['name'] .= (string) ($fragment['function']['name'] ?? '');
                $calls[$i]['function']['arguments'] .= (string) ($fragment['function']['arguments'] ?? '');
            }
        };

        $ch = curl_init($endpoint . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: text/event-stream', 'api-key: ' . $apiKey],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$buffer, &$raw, &$failure, $handleLine): int {
                if (strlen($raw) < 4000) {
                    $raw .= $chunk;
                }
                $buffer .= $chunk;
                try {
                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = rtrim(substr($buffer, 0, $pos), "\r");
                        $buffer = substr($buffer, $pos + 1);
                        $handleLine($line);
                    }
                } catch (\Throwable $e) {
                    $failure = $e; // e.g. client disconnected: stop the transfer and rethrow after curl_exec
                    return 0;
                }

                return strlen($chunk);
            },
        ]);
        $ok = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($failure instanceof \Throwable) {
            throw $failure;
        }
        if ($ok === false) {
            throw new RuntimeException('Không gọi được Azure OpenAI: ' . $error);
        }
        if ($status >= 400) {
            $json = json_decode($raw, true);
            $detail = is_array($json) ? (string) ($json['error']['message'] ?? '') : substr($raw, 0, 200);
            throw new RuntimeException(sprintf('Azure OpenAI lỗi HTTP %d: %s', $status, $detail));
        }
        if (trim($buffer) !== '') {
            $handleLine(rtrim($buffer, "\r\n"));
        }

        $message = ['role' => 'assistant', 'content' => $content !== '' ? $content : null];
        if ($calls !== []) {
            ksort($calls);
            $message['tool_calls'] = array_values($calls);
        }

        return $message;
    }

    /** @return array<string, mixed> */
    private function request(string $path, array $payload): array
    {
        $endpoint = rtrim($this->required('AZURE_OPENAI_ENDPOINT'), '/');
        $apiKey = $this->required('AZURE_OPENAI_API_KEY');

        $ch = curl_init($endpoint . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'api-key: ' . $apiKey],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('Không gọi được Azure OpenAI: ' . $error);
        }
        $json = json_decode((string) $body, true);
        if ($status >= 400 || !is_array($json)) {
            $detail = is_array($json) ? (string) ($json['error']['message'] ?? '') : substr((string) $body, 0, 200);
            throw new RuntimeException(sprintf('Azure OpenAI lỗi HTTP %d: %s', $status, $detail));
        }

        return $json;
    }

    private function required(string $key): string
    {
        $value = Env::get($key);
        if ($value === null || $value === '') {
            throw new RuntimeException("Thiếu cấu hình {$key} trong .env.");
        }

        return $value;
    }
}
