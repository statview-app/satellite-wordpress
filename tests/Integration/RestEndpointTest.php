<?php

declare(strict_types=1);

use Statview\Satellite\Config;
use Statview\Satellite\Widgets\Widget;

/**
 * End-to-end tests against a real WordPress REST server. The plugin's routes
 * and permission callbacks run through the genuine WP pipeline here, so this
 * covers the wiring that the mocked unit suite cannot.
 */

function dispatch(string $method, string $route, ?string $token = null): WP_REST_Response
{
    $request = new WP_REST_Request($method, $route);

    if ($token !== null) {
        $request->set_header('Authorization', 'Bearer ' . $token);
    }

    return rest_get_server()->dispatch($request);
}

beforeEach(function (): void {
    update_option(Config::OPTION, [
        'dsn' => 'https://statview.app/1/secret-token',
        'endpoint' => 'https://statview.app',
        'project_id' => '1',
        'api_key' => 'secret-token',
    ]);

    // Ensure the routes are registered on the active REST server.
    do_action('rest_api_init');
});

it('registers the satellite routes', function (): void {
    $routes = rest_get_server()->get_routes();

    expect($routes)->toHaveKey('/statview/v1/stats')
        ->and($routes)->toHaveKey('/statview/v1/about');
});

it('rejects /stats without a bearer token', function (): void {
    expect(dispatch('GET', '/statview/v1/stats')->get_status())->toBe(403);
});

it('rejects /stats with the wrong bearer token', function (): void {
    expect(dispatch('GET', '/statview/v1/stats', 'wrong')->get_status())->toBe(403);
});

it('returns widgets for /stats with the correct token', function (): void {
    self::factory()->post->create_many(3, ['post_status' => 'publish']);

    $response = dispatch('GET', '/statview/v1/stats', 'secret-token');
    $data = $response->get_data();

    expect($response->get_status())->toBe(200)
        ->and($data)->toHaveKey('widgets');

    $byCode = [];
    foreach ($data['widgets'] as $widget) {
        $byCode[$widget['code']] = $widget;
    }

    expect($byCode)->toHaveKey('wp_published_posts')
        ->and($byCode['wp_published_posts']['value'])->toBeGreaterThanOrEqual(3);
});

it('returns categorised metadata for /about with the correct token', function (): void {
    $response = dispatch('GET', '/statview/v1/about', 'secret-token');
    $data = $response->get_data();

    expect($response->get_status())->toBe(200)
        ->and($data)->toHaveKey('data')
        ->and($data['data'])->toHaveKey('Environment')
        ->and($data['data']['Environment'])->toHaveKey('PHP Version')
        ->and($data['data']['Environment'])->toHaveKey('WordPress Version');
});

it('exposes widgets registered through the statview_widgets filter', function (): void {
    add_filter('statview_widgets', static function (array $widgets): array {
        $widgets[] = Widget::make('integration_metric')->title('Integration')->value(123);

        return $widgets;
    });

    $data = dispatch('GET', '/statview/v1/stats', 'secret-token')->get_data();
    $codes = array_column($data['widgets'], 'code');

    expect($codes)->toContain('integration_metric');
});
