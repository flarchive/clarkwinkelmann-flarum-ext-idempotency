<?php

namespace ClarkWinkelmann\Idempotency\Exceptions;

use Flarum\Foundation\KnownError;

class IdempotentNonJsonNotSupported extends \Exception implements KnownError
{
    public function getType(): string
    {
        return 'idempotent_non_json_not_supported';
    }
}
