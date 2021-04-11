<?php

namespace ClarkWinkelmann\Idempotency\Exceptions;

use Flarum\Foundation\KnownError;

class RequestBodyDifferent extends \Exception implements KnownError
{
    public function getType(): string
    {
        return 'idempotent_request_body_different';
    }
}
