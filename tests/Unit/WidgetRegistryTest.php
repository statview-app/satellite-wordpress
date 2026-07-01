<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Statview\Satellite\Widgets\Widget;
use Statview\Satellite\Widgets\WidgetRegistry;

beforeEach(function (): void {
    // Stub the WordPress functions the default collectors read from.
    Functions\when('wp_count_posts')->justReturn((object) ['publish' => 7]);
    Functions\when('wp_count_comments')->justReturn((object) ['approved' => 3, 'moderated' => 1]);
    Functions\when('count_users')->justReturn(['total_users' => 5]);
    Functions\when('wp_get_update_data')->justReturn(['counts' => ['total' => 2, 'wordpress' => 0, 'plugins' => 2, 'themes' => 0]]);

    // apply_filters($tag, $value, ...$args) returns the (possibly filtered) value.
    Functions\when('apply_filters')->returnArg(2);
});

it('includes the default core and update widgets', function (): void {
    $codes = array_column((new WidgetRegistry())->toArray(), 'code');

    expect($codes)
        ->toContain('wp_published_posts')
        ->toContain('wp_total_users')
        ->toContain('wp_updates_total');
});

it('omits WooCommerce widgets when WooCommerce is not active', function (): void {
    // class_exists('WooCommerce') is false in the test runtime.
    $codes = array_column((new WidgetRegistry())->toArray(), 'code');

    expect($codes)->not->toContain('woocommerce_orders_today');
});

it('exposes widgets added through the statview_widgets filter', function (): void {
    Functions\when('apply_filters')->alias(function (string $tag, array $widgets) {
        $widgets[] = Widget::make('custom_metric')->title('Custom')->value(99);

        return $widgets;
    });

    $stats = (new WidgetRegistry())->toArray();
    $custom = array_values(array_filter($stats, static fn ($w) => $w['code'] === 'custom_metric'));

    expect($custom)->toHaveCount(1)
        ->and($custom[0]['value'])->toBe(99);
});

it('drops filter entries that are not Widget instances', function (): void {
    Functions\when('apply_filters')->alias(function (string $tag, array $widgets) {
        $widgets[] = 'not-a-widget';
        $widgets[] = ['also' => 'invalid'];

        return $widgets;
    });

    $stats = (new WidgetRegistry())->toArray();

    // Every surviving entry must be a normalised widget array with a code.
    foreach ($stats as $widget) {
        expect($widget)->toHaveKey('code');
    }
});
