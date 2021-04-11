# Idempotency

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/clarkwinkelmann/flarum-ext-idempotency.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-idempotency) [![Total Downloads](https://img.shields.io/packagist/dt/clarkwinkelmann/flarum-ext-idempotency.svg)](https://packagist.org/packages/clarkwinkelmann/flarum-ext-idempotency) [![Donate](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.me/clarkwinkelmann)

This extension is meant primarily as a proof of concept, but is fully working.
Let me know which use cases you find for it!

It implements optionally idempotent POST requests to Flarum REST API.

It then modifies the Flarum frontend so discussion and post creation uses them.

Idempotency is requested by passing a `Idempotency-Key` header with a unique value.
If another request is made with the same key, it will be ignored and the previous response will be returned again.

Key aspects:

- Can be used on all POST routes of the REST API, including those registered by extensions.
- Only works with requests returning an instanceof Laminas' `JsonResponse`. Attempting to use the idempotency key on a different response type will return in a 400 error, but the request will still be performed!
- The key has to be unique per actor. Guests cannot use idempotency keys.
- If the body of the request does not match the original request with the same idempotency key, a 400 error will be thrown.
- Only successful requests are cached. It's assumed that any thrown exception was before any data was saved.
- Cache is configured to 24h and is currently not configurable.

Concerns:

- The extension cannot differentiate between exceptions happening before or after data saving. The choice was made to not cache those requests so they can be attempted again. You should use an async queue to reduce the risk of this happening.
- The default filesystem cache driver of Flarum will quickly fill with entries and I don't think it auto-cleans expired files. You should probably use a different cache driver on any important forum.
- Extensions that add headers or modify JSON responses based on some internal state through middlewares might be impacted. It might be necessary to change the boot order of extensions depending on the situation.
- Race conditions could allow two requests made at the same time to be both executed. Using an async queue driver will help with this issue as the sooner the first request finishes, the sooner a cached response will be available for the second request.

## Installation

Make sure you understand the description above. Most likely you don't need/want that extension.

    composer require clarkwinkelmann/flarum-ext-idempotency

## Support

This extension is under **minimal maintenance**.

It was developed for a client and released as open-source for the benefit of the community.
I might publish simple bugfixes or compatibility updates for free.

You can [contact me](https://clarkwinkelmann.com/flarum) to sponsor additional features or updates.

Support is offered on a "best effort" basis through the Flarum community thread.

## Links

- [GitHub](https://github.com/clarkwinkelmann/flarum-ext-idempotency)
- [Packagist](https://packagist.org/packages/clarkwinkelmann/flarum-ext-idempotency)
