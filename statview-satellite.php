<?php
/**
 * Plugin Name:       Statview Satellite
 * Plugin URI:        https://statview.app
 * Description:       Sets up the communication channel between your WordPress site and Statview for monitoring, stats and announcements.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Statview
 * Author URI:        https://statview.app
 * License:           MIT
 * Text Domain:       statview-satellite
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('STATVIEW_SATELLITE_VERSION', '1.0.0');
define('STATVIEW_SATELLITE_FILE', __FILE__);
define('STATVIEW_SATELLITE_DIR', plugin_dir_path(__FILE__));

/*
 * Lightweight PSR-4 autoloader for the Statview\Satellite namespace.
 *
 * The distributed .zip ships without a vendor/ directory, so we cannot rely on
 * Composer's autoloader being present. This maps Statview\Satellite\Foo\Bar to
 * src/Foo/Bar.php, mirroring the PSR-4 mapping declared in composer.json (which
 * is only used for local development and tests).
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'Statview\\Satellite\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = STATVIEW_SATELLITE_DIR.'src/'.str_replace('\\', '/', $relative).'.php';

    if (is_readable($path)) {
        require $path;
    }
});

require_once STATVIEW_SATELLITE_DIR.'src/functions.php';

register_activation_hook(__FILE__, [\Statview\Satellite\Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [\Statview\Satellite\Plugin::class, 'deactivate']);

add_action('plugins_loaded', static function (): void {
    \Statview\Satellite\Plugin::instance()->boot();
});
