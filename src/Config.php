<?php

declare(strict_types=1);

namespace Statview\Satellite;

/**
 * Reads the plugin settings and exposes the resolved connection details.
 *
 * The Laravel package encodes everything in a single STATVIEW_DSN of the form
 * scheme://host/{project_id}/{api_key}. We keep that exact format so the same
 * DSN shown in the Statview panel works for both Laravel and WordPress sites.
 */
final class Config
{
    public const OPTION = 'statview_settings';

    /**
     * @return array{dsn:string,endpoint:string,project_id:string,api_key:string}
     */
    private function settings(): array
    {
        $stored = get_option(self::OPTION, []);

        return array_merge([
            'dsn' => '',
            'endpoint' => '',
            'project_id' => '',
            'api_key' => '',
        ], is_array($stored) ? $stored : []);
    }

    public function dsn(): string
    {
        return (string) $this->settings()['dsn'];
    }

    public function endpoint(): string
    {
        return (string) $this->settings()['endpoint'];
    }

    public function projectId(): string
    {
        return (string) $this->settings()['project_id'];
    }

    public function apiKey(): string
    {
        return (string) $this->settings()['api_key'];
    }

    public function isConfigured(): bool
    {
        $settings = $this->settings();

        return $settings['endpoint'] !== '' && $settings['project_id'] !== '' && $settings['api_key'] !== '';
    }

    /**
     * Parse a DSN into its components. Returns null when the DSN is malformed.
     *
     * Mirrors SatelliteServiceProvider::registerDsn() in the Laravel package:
     * the path segments are {project_id}/{api_key} and the endpoint is the
     * scheme + host (+ optional port).
     *
     * @return array{endpoint:string,project_id:string,api_key:string}|null
     */
    public static function parseDsn(string $dsn): ?array
    {
        $dsn = trim($dsn);

        if ($dsn === '') {
            return null;
        }

        $parts = wp_parse_url($dsn);

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $parts['path']), static fn (string $s): bool => $s !== ''));

        if (count($segments) < 2) {
            return null;
        }

        $endpoint = $parts['scheme'].'://'.$parts['host'];

        if (! empty($parts['port'])) {
            $endpoint .= ':'.$parts['port'];
        }

        return [
            'endpoint' => $endpoint,
            'project_id' => $segments[0],
            'api_key' => $segments[1],
        ];
    }

    /**
     * Normalise a raw settings array (from the settings form) into the stored
     * shape, parsing the DSN into its components.
     *
     * @param array{dsn?:string} $input
     * @return array{dsn:string,endpoint:string,project_id:string,api_key:string}
     */
    public static function sanitize(array $input): array
    {
        $dsn = isset($input['dsn']) ? trim((string) $input['dsn']) : '';
        $parsed = self::parseDsn($dsn);

        return [
            'dsn' => $dsn,
            'endpoint' => $parsed['endpoint'] ?? '',
            'project_id' => $parsed['project_id'] ?? '',
            'api_key' => $parsed['api_key'] ?? '',
        ];
    }
}
