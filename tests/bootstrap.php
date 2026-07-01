<?php

declare(strict_types=1);

/*
 * Bootstrap for the WordPress integration suite.
 *
 * Boots a real WordPress test instance (via wp-phpunit) and loads the plugin
 * as a must-use plugin so its hooks are registered during the normal WP boot.
 */

$_tests_dir = getenv('WP_TESTS_DIR');

if (! $_tests_dir) {
    // wp-phpunit ships the WordPress test framework via Composer.
    $_tests_dir = dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';
}

// Point the test suite at our env-driven DB config unless one is already set.
if (! getenv('WP_TESTS_CONFIG_PATH')) {
    putenv('WP_TESTS_CONFIG_PATH=' . __DIR__ . '/wp-tests-config.php');
}

require dirname(__DIR__) . '/vendor/autoload.php';

if (! file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "Could not find the WordPress test library at {$_tests_dir}.\n");
    exit(1);
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter('muplugins_loaded', static function (): void {
    require dirname(__DIR__) . '/statview-satellite.php';
});

require $_tests_dir . '/includes/bootstrap.php';
