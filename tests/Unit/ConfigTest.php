<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Statview\Satellite\Config;

beforeEach(function (): void {
    // wp_parse_url behaves like PHP's parse_url for our purposes.
    Functions\when('wp_parse_url')->alias('parse_url');
});

it('parses a well-formed DSN into its components', function (): void {
    $parsed = Config::parseDsn('https://statview.app/123abc/secret-key');

    expect($parsed)->toBe([
        'endpoint' => 'https://statview.app',
        'project_id' => '123abc',
        'api_key' => 'secret-key',
    ]);
});

it('preserves a custom port in the endpoint', function (): void {
    $parsed = Config::parseDsn('http://localhost:8000/7/token');

    expect($parsed['endpoint'])->toBe('http://localhost:8000')
        ->and($parsed['project_id'])->toBe('7')
        ->and($parsed['api_key'])->toBe('token');
});

it('returns null for a malformed DSN', function (string $dsn): void {
    expect(Config::parseDsn($dsn))->toBeNull();
})->with([
    'empty' => '',
    'no path' => 'https://statview.app',
    'missing api key' => 'https://statview.app/123abc',
    'no scheme' => 'statview.app/123abc/key',
]);

it('sanitizes form input by exploding the DSN', function (): void {
    $sanitized = Config::sanitize(['dsn' => 'https://statview.app/9/abc']);

    expect($sanitized)->toBe([
        'dsn' => 'https://statview.app/9/abc',
        'endpoint' => 'https://statview.app',
        'project_id' => '9',
        'api_key' => 'abc',
    ]);
});

it('sanitizes invalid input to empty components', function (): void {
    $sanitized = Config::sanitize(['dsn' => 'not-a-dsn']);

    expect($sanitized['endpoint'])->toBe('')
        ->and($sanitized['project_id'])->toBe('')
        ->and($sanitized['api_key'])->toBe('');
});
