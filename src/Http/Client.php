<?php

declare(strict_types=1);

namespace Statview\Satellite\Http;

use Statview\Satellite\Config;
use WP_Error;

/**
 * Thin wrapper around the WordPress HTTP API for outbound calls to Statview.
 *
 * Equivalent of the Laravel package's Http::statviewClient() macro: it targets
 * {endpoint}/api/ and attaches the Bearer api_key on every request.
 */
final class Client
{
    public function __construct(private readonly Config $config) {}

    /**
     * @param array<string,mixed> $body
     * @return array<string,mixed>|null Decoded JSON response, or null on failure.
     */
    public function post(string $path, array $body): ?array
    {
        $response = wp_remote_post($this->url($path), [
            'timeout' => 10,
            'headers' => $this->headers(),
            'body' => wp_json_encode($body),
        ]);

        return $this->decode($response);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function get(string $path): ?array
    {
        $response = wp_remote_get($this->url($path), [
            'timeout' => 10,
            'headers' => $this->headers(),
        ]);

        return $this->decode($response);
    }

    private function url(string $path): string
    {
        return rtrim($this->config->endpoint(), '/') . '/api/' . ltrim($path, '/');
    }

    /**
     * @return array<string,string>
     */
    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->config->apiKey(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Statview-Client' => 'wordpress',
            'User-Agent' => 'Statview-Satellite-WordPress',
        ];
    }

    /**
     * @param array<string,mixed>|WP_Error $response
     * @return array<string,mixed>|null
     */
    private function decode($response): ?array
    {
        if (is_wp_error($response)) {
            error_log('[Statview Satellite] ' . $response->get_error_message());

            return null;
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);

        return is_array($decoded) ? $decoded : null;
    }
}
