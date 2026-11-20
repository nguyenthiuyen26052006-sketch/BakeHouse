<?php

declare(strict_types=1);

namespace App\Core;

/** File-based sliding-window limiter (shared across sessions, so clearing cookies does not reset it). */
final class RateLimiter
{
    /** Returns true and records the hit when under the limit; false when the limit is reached. */
    public static function hit(string $key, int $limit, int $windowSeconds): bool
    {
        $dir = BASE_PATH . '/storage/cache';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return true; // fail open: storage unavailable must not take the chatbot down
        }

        $handle = @fopen($dir . '/rl_' . hash('sha256', $key) . '.json', 'c+');
        if ($handle === false) {
            return true;
        }

        try {
            flock($handle, LOCK_EX);
            $now = time();
            $hits = json_decode((string) stream_get_contents($handle), true);
            $hits = array_values(array_filter(
                is_array($hits) ? $hits : [],
                static fn (mixed $t): bool => is_int($t) && $t > $now - $windowSeconds
            ));

            $allowed = count($hits) < $limit;
            if ($allowed) {
                $hits[] = $now;
            }
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($hits));

            return $allowed;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
