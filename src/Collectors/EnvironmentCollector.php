<?php

declare(strict_types=1);

namespace Statview\Satellite\Collectors;

/**
 * Builds the environment metadata exposed on /about.
 *
 * The returned array is keyed by category (Environment, WordPress, Drivers, ...)
 * because the Statview server stores array_keys() as "about_categories" and
 * flattens "Category.key" pairs into meta widgets. Every value is a scalar so
 * it renders cleanly after Arr::dot() on the server.
 */
final class EnvironmentCollector
{
    /**
     * @return array<string,array<string,scalar>>
     */
    public function collect(): array
    {
        global $wp_version;

        $theme = wp_get_theme();

        return [
            'Environment' => [
                'PHP Version' => PHP_VERSION,
                'WordPress Version' => (string) $wp_version,
                'Web Server' => $this->webServer(),
                'Debug Mode' => $this->bool(defined('WP_DEBUG') && WP_DEBUG),
                'Multisite' => $this->bool(is_multisite()),
                'HTTPS' => $this->bool(is_ssl()),
                'Locale' => (string) get_locale(),
                'Timezone' => (string) wp_timezone_string(),
                'Memory Limit' => (string) WP_MEMORY_LIMIT,
            ],
            'WordPress' => [
                'Site URL' => (string) get_site_url(),
                'Home URL' => (string) get_home_url(),
                'Active Theme' => (string) $theme->get('Name'),
                'Theme Version' => (string) $theme->get('Version'),
                'Active Plugins' => (string) count($this->activePlugins()),
            ],
            'Drivers' => [
                'Database' => $this->databaseVersion(),
                'Object Cache' => $this->bool(wp_using_ext_object_cache()),
            ],
            'Plugins' => $this->activePlugins(),
        ];
    }

    /**
     * @return array<string,string> map of plugin name => version
     */
    private function activePlugins(): array
    {
        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all = get_plugins();
        $active = (array) get_option('active_plugins', []);
        $result = [];

        foreach ($active as $file) {
            if (isset($all[$file])) {
                $result[(string) $all[$file]['Name']] = (string) $all[$file]['Version'];
            }
        }

        return $result;
    }

    private function databaseVersion(): string
    {
        global $wpdb;

        if (isset($wpdb) && method_exists($wpdb, 'db_version')) {
            return (string) $wpdb->db_version();
        }

        return 'unknown';
    }

    private function webServer(): string
    {
        return isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field((string) $_SERVER['SERVER_SOFTWARE']) : 'unknown';
    }

    /**
     * Some WordPress predicates (e.g. wp_using_ext_object_cache()) can return
     * null when queried before they are initialised, so accept any value and
     * coerce it — a strict bool type hint would fatal the whole /about response.
     *
     * @param  mixed  $value
     */
    private function bool($value): string
    {
        return $value ? 'true' : 'false';
    }
}
