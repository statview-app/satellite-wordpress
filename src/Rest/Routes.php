<?php

declare(strict_types=1);

namespace Statview\Satellite\Rest;

use Statview\Satellite\Config;

/**
 * Registers the inbound REST endpoints the Statview server polls.
 *
 * These mirror the Laravel package routes (about, stats). With a project path
 * of "wp-json/statview/v1" on the Statview side, the server hits:
 *   GET {site}/wp-json/statview/v1/about
 *   GET {site}/wp-json/statview/v1/stats
 */
final class Routes
{
    public const NAMESPACE = 'statview/v1';

    public function register(): void
    {
        $auth = new Auth(new Config());
        $permission = [$auth, 'check'];

        register_rest_route(self::NAMESPACE, '/about', [
            'methods' => 'GET',
            'callback' => [new AboutController(), 'handle'],
            'permission_callback' => $permission,
        ]);

        register_rest_route(self::NAMESPACE, '/stats', [
            'methods' => 'GET',
            'callback' => [new StatsController(), 'handle'],
            'permission_callback' => $permission,
        ]);
    }
}
