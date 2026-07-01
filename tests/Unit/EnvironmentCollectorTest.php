<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Statview\Satellite\Collectors\EnvironmentCollector;

beforeEach(function (): void {
    if (! defined('WP_MEMORY_LIMIT')) {
        define('WP_MEMORY_LIMIT', '256M');
    }

    $GLOBALS['wp_version'] = '6.5';

    // A theme stub exposing the get() accessor the collector calls.
    $theme = new class
    {
        public function get(string $key): string
        {
            return match ($key) {
                'Name' => 'Twenty Twenty-Four',
                'Version' => '1.2',
                default => '',
            };
        }
    };

    Functions\when('wp_get_theme')->justReturn($theme);
    Functions\when('is_multisite')->justReturn(false);
    Functions\when('is_ssl')->justReturn(true);
    Functions\when('get_locale')->justReturn('en_US');
    Functions\when('wp_timezone_string')->justReturn('UTC');
    Functions\when('get_site_url')->justReturn('https://example.test');
    Functions\when('get_home_url')->justReturn('https://example.test');
    Functions\when('get_plugins')->justReturn([
        'akismet/akismet.php' => ['Name' => 'Akismet', 'Version' => '5.3'],
    ]);
    Functions\when('get_option')->justReturn(['akismet/akismet.php']);
});

it('does not fatal when wp_using_ext_object_cache() returns null', function (): void {
    // WordPress returns null for this predicate when it is queried before the
    // object cache is initialised — a strict bool hint would fatal /about.
    Functions\when('wp_using_ext_object_cache')->justReturn(null);

    $about = (new EnvironmentCollector())->collect();

    expect($about['Drivers']['Object Cache'])->toBe('false');
});

it('collects the expected categories including active plugins', function (): void {
    Functions\when('wp_using_ext_object_cache')->justReturn(true);

    $about = (new EnvironmentCollector())->collect();

    expect(array_keys($about))->toBe(['Environment', 'WordPress', 'Drivers', 'Plugins'])
        ->and($about['Environment']['WordPress Version'])->toBe('6.5')
        ->and($about['Drivers']['Object Cache'])->toBe('true')
        ->and($about['Plugins'])->toBe(['Akismet' => '5.3']);
});
