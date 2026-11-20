<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

/**
 * Sits between the model's token stream and the browser: the model ends its answer with machine markers
 * ("IDS: 1,2" / "BUDGET: 300000") that must never be shown. Text is released as soon as it is known not to be
 * (the start of) a marker; everything from a marker onward is dropped.
 */
final class StreamTextFilter
{
    private const MARKER = '/\b(?:IDS?|BUDGET)[ \t]*[:=]/iu';
    // A tail that could still grow into a marker once more tokens arrive.
    private const POSSIBLE_PREFIX = '/\b(?:I|ID|IDS|B|BU|BUD|BUDG|BUDGE|BUDGET)[ \t]*$/iu';

    private string $buffer = '';
    private bool $stopped = false;

    public function push(string $token): string
    {
        if ($this->stopped) {
            return '';
        }
        $this->buffer .= $token;

        if (preg_match(self::MARKER, $this->buffer, $m, PREG_OFFSET_CAPTURE) === 1) {
            $out = substr($this->buffer, 0, (int) $m[0][1]);
            $this->buffer = '';
            $this->stopped = true;

            return $out;
        }

        if (preg_match(self::POSSIBLE_PREFIX, $this->buffer, $m, PREG_OFFSET_CAPTURE) === 1) {
            $cut = (int) $m[0][1];
            $out = substr($this->buffer, 0, $cut);
            $this->buffer = substr($this->buffer, $cut);

            return $out;
        }

        $out = $this->buffer;
        $this->buffer = '';

        return $out;
    }

    /** Releases whatever was held back (call when the model finished and no marker materialised). */
    public function flush(): string
    {
        $out = $this->stopped ? '' : $this->buffer;
        $this->buffer = '';

        return $out;
    }
}
