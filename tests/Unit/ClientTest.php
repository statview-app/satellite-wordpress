<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Statview\Satellite\Config;
use Statview\Satellite\Http\Client;
use Statview\Satellite\Services\Timeline;

beforeEach(function (): void {
    Functions\when('get_option')->justReturn([
        'dsn' => '',
        'endpoint' => 'https://statview.app',
        'project_id' => '42',
        'api_key' => 'secret',
    ]);
    Functions\when('wp_json_encode')->alias('json_encode');
    Functions\when('wp_remote_retrieve_body')->justReturn('{"id":1}');
});

it('targets the /api/ base path and attaches the bearer header', function (): void {
    $captured = [];

    Functions\when('wp_remote_post')->alias(function (string $url, array $args) use (&$captured) {
        $captured = ['url' => $url, 'args' => $args];

        return ['body' => '{"id":1}'];
    });

    $result = (new Client(new Config()))->post('timeline/42', ['title' => 'Hi']);

    expect($captured['url'])->toBe('https://statview.app/api/timeline/42')
        ->and($captured['args']['headers']['Authorization'])->toBe('Bearer secret')
        ->and($captured['args']['headers']['Content-Type'])->toBe('application/json')
        ->and($result)->toBe(['id' => 1]);
});

it('returns null and logs when the request errors', function (): void {
    Functions\when('wp_remote_post')->justReturn(new WP_Error('http', 'boom'));

    $result = (new Client(new Config()))->post('timeline/42', []);

    expect($result)->toBeNull();
});

it('normalises an unknown timeline type to default with its icon', function (): void {
    $captured = [];

    Functions\when('wp_remote_post')->alias(function (string $url, array $args) use (&$captured) {
        $captured = json_decode($args['body'], true);

        return ['body' => '{"id":1}'];
    });

    $config = new Config();
    (new Timeline($config, new Client($config)))->post('Title', 'Body', 'bogus');

    expect($captured['type'])->toBe('default')
        ->and($captured['icon'])->toBe('📣');
});
