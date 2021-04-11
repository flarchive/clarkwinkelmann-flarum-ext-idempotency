<?php

namespace ClarkWinkelmann\Idempotency;

use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js'),

    (new Extend\Middleware('api'))
        ->add(Middlewares\IdempotencyMiddleware::class),

    (new Extend\ErrorHandling())
        ->status('idempotent_non_json_not_supported', 400)
        ->status('idempotent_request_body_different', 400),
];
