<?php

namespace ClarkWinkelmann\Idempotency\Middlewares;

use ClarkWinkelmann\Idempotency\CachedResponse;
use ClarkWinkelmann\Idempotency\Exceptions\IdempotentNonJsonNotSupported;
use ClarkWinkelmann\Idempotency\Exceptions\RequestBodyDifferent;
use Flarum\User\User;
use Illuminate\Contracts\Cache\Repository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IdempotencyMiddleware implements MiddlewareInterface
{
    protected $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var $actor User
         */
        $actor = $request->getAttribute('actor');

        if ($actor->isGuest()) {
            return $handler->handle($request);
        }

        if ($request->getMethod() !== 'POST') {
            return $handler->handle($request);
        }

        $idempotencyKey = $request->getHeaderLine('Idempotency-Key');

        if (!$idempotencyKey) {
            return $handler->handle($request);
        }

        $cacheKey = serialize([
            $actor->id,
            $request->getUri()->getPath(),
            $idempotencyKey,
        ]);

        /**
         * @var $cached CachedResponse
         */
        $cached = $this->cache->get($cacheKey);

        if ($cached) {
            if (serialize($cached->parsedBody) !== serialize($request->getParsedBody())) {
                throw new RequestBodyDifferent();
            }

            return new JsonResponse($cached->payload, $cached->statusCode, $cached->headers);
        }

        $response = $handler->handle($request);

        if ($response instanceof JsonResponse) {
            $cached = new CachedResponse();
            $cached->parsedBody = $request->getParsedBody();
            $cached->payload = $response->getPayload();
            $cached->statusCode = $response->getStatusCode();
            $cached->headers = $response->getHeaders();

            $this->cache->put($cacheKey, $cached, 24 * 60 * 60);
        } else {
            throw new IdempotentNonJsonNotSupported();
        }

        return $response;
    }
}
