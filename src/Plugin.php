<?php

declare(strict_types=1);

namespace Statview\Satellite;

use Statview\Satellite\Admin\SettingsPage;
use Statview\Satellite\Rest\Routes;
use Statview\Satellite\Services\Announcements;

/**
 * Central bootstrap for the Statview Satellite plugin.
 *
 * Mirrors the responsibilities of the Laravel package's SatelliteServiceProvider:
 * it wires up the inbound REST routes, the admin settings page and the
 * announcement polling cron.
 */
final class Plugin
{
    private static ?Plugin $instance = null;

    public const CRON_HOOK = 'statview_satellite_fetch_announcements';

    private function __construct() {}

    public static function instance(): Plugin
    {
        if (! self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        add_action('rest_api_init', [new Routes(), 'register']);

        if (is_admin()) {
            (new SettingsPage())->register();
        }

        $announcements = new Announcements(new Config());
        add_action(self::CRON_HOOK, [$announcements, 'refresh']);
        add_action('admin_notices', [$announcements, 'renderNotices']);
    }

    public static function activate(): void
    {
        if (! wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + 60, 'hourly', self::CRON_HOOK);
        }
    }

    public static function deactivate(): void
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);

        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }
}
