<?php

declare(strict_types=1);

namespace Statview\Satellite\Rest;

use Statview\Satellite\Config;
use WP_Error;
use WP_REST_Request;

/**
 * Authenticates inbound requests from the Statview server.
 *
 * Equivalent of the Laravel package's ValidateRequest middleware: the request
 * must carry an "Authorization: Bearer {api_key}" header whose token matches
 * the configured api_key. We compare in constant time via hash_equals().
 */
final class Auth
{
    public function __construct(private readonly Config $config) {}

    /**
     * @return true|WP_Error
     */
    public function check(WP_REST_Request $request)
    {
        $expected = $this->config->apiKey();

        if ($expected === '') {
            return new WP_Error('statview_not_configured', 'Statview Satellite is not configured.', ['status' => 403]);
        }

        if (! hash_equals($expected, $this->bearerToken($request))) {
            return new WP_Error('statview_forbidden', 'Invalid token.', ['status' => 403]);
        }

        return true;
    }

    private function bearerToken(WP_REST_Request $request): string
    {
        $header = (string) $request->get_header('authorization');

        if (stripos($header, 'Bearer ') === 0) {
            return trim(substr($header, 7));
        }

        return '';
    }
}
