<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, string|array<int, string>> $rules e.g. ['email' => 'required|email|max:190']
     * @param array<string, callable(mixed): bool> $customRules named callbacks usable as rule names
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly array $customRules = []
    ) {
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleSet) {
            $ruleList = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;
            $isEmpty = $value === null || (is_string($value) && trim($value) === '');

            foreach ($ruleList as $rule) {
                [$name, $argument] = array_pad(explode(':', $rule, 2), 2, null);

                if ($name !== 'required' && $isEmpty) {
                    continue; // optional empty values skip other rules
                }

                if (!$this->passes((string) $name, $field, $value, $argument)) {
                    $this->errors[$field] = $this->message((string) $name, $field, $argument);
                    break;
                }
            }
        }

        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    private function passes(string $rule, string $field, mixed $value, ?string $argument): bool
    {
        $text = is_scalar($value) ? (string) $value : '';

        return match ($rule) {
            'required' => !($value === null || (is_string($value) && trim($value) === '')),
            'email' => filter_var($text, FILTER_VALIDATE_EMAIL) !== false,
            'phone' => (bool) preg_match('/^(0|\+84)\d{9,10}$/', $text),
            'min' => mb_strlen($text) >= (int) $argument,
            'max' => mb_strlen($text) <= (int) $argument,
            'numeric' => is_numeric($text),
            'integer' => filter_var($text, FILTER_VALIDATE_INT) !== false,
            'in' => in_array($text, explode(',', (string) $argument), true),
            'confirmed' => $value === ($this->data[$field . '_confirmation'] ?? null),
            'regex' => (bool) preg_match((string) $argument, $text),
            'password' => (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/u', $text),
            default => isset($this->customRules[$rule]) ? (bool) ($this->customRules[$rule])($value) : true,
        };
    }

    private function message(string $rule, string $field, ?string $argument): string
    {
        $label = lang('validation.attributes.' . $field, $field);
        $template = lang('validation.' . $rule, 'Giá trị :attribute không hợp lệ.');

        return str_replace([':attribute', ':arg'], [$label, (string) $argument], $template);
    }
}
