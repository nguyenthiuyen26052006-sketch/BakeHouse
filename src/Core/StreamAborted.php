<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** Thrown while streaming when the client has gone away, so upstream work can stop early. */
final class StreamAborted extends RuntimeException
{
}
