<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Statview\Satellite\Config;
use Statview\Satellite\Rest\Auth;

function makeConfig(string $apiKey): Config
{
    Functions\when('get_option')->justReturn([
        'dsn' => '',
        'endpoint' => 'https://statview.app',
        'project_id' => '1',
        'api_key' => $apiKey,
    ]);

    return new Config();
}

it('accepts a request whose bearer token matches the api key', function (): void {
    $auth = new Auth(makeConfig('secret'));
    $request = new WP_REST_Request(['authorization' => 'Bearer secret']);

    expect($auth->check($request))->toBeTrue();
});

it('rejects a request with the wrong token', function (): void {
    $auth = new Auth(makeConfig('secret'));
    $request = new WP_REST_Request(['authorization' => 'Bearer nope']);

    $result = $auth->check($request);

    expect($result)->toBeInstanceOf(WP_Error::class)
        ->and($result->data['status'])->toBe(403);
});

it('rejects a request with no authorization header', function (): void {
    $auth = new Auth(makeConfig('secret'));
    $request = new WP_REST_Request([]);

    expect($auth->check($request))->toBeInstanceOf(WP_Error::class);
});

it('rejects all requests when no api key is configured', function (): void {
    $auth = new Auth(makeConfig(''));
    $request = new WP_REST_Request(['authorization' => 'Bearer anything']);

    expect($auth->check($request))->toBeInstanceOf(WP_Error::class);
});
