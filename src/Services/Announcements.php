<?php

declare(strict_types=1);

namespace Statview\Satellite\Services;

use Statview\Satellite\Config;
use Statview\Satellite\Http\Client;

/**
 * Fetches and renders Statview announcements.
 *
 * Targets GET /api/announcements/{project_id}. Results are cached in a
 * transient and refreshed on a WP-Cron schedule, then surfaced as admin
 * notices within their active time window.
 */
final class Announcements
{
    public const TRANSIENT = 'statview_announcements';

    public function __construct(
        private readonly Config $config,
        private readonly ?Client $client = null,
    ) {}

    public function refresh(): void
    {
        if (! $this->config->isConfigured()) {
            return;
        }

        $client = $this->client ?? new Client($this->config);
        $response = $client->get('announcements/' . $this->config->projectId());

        $data = is_array($response) && isset($response['data']) && is_array($response['data'])
            ? $response['data']
            : [];

        set_transient(self::TRANSIENT, $data, HOUR_IN_SECONDS);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function active(): array
    {
        $stored = get_transient(self::TRANSIENT);
        $announcements = is_array($stored) ? $stored : [];
        $now = time();

        return array_values(array_filter($announcements, static function ($announcement) use ($now): bool {
            if (! is_array($announcement)) {
                return false;
            }

            $startsAt = ! empty($announcement['starts_at']) ? strtotime((string) $announcement['starts_at']) : null;
            $endsAt = ! empty($announcement['ends_at']) ? strtotime((string) $announcement['ends_at']) : null;

            if ($startsAt !== null && $startsAt > $now) {
                return false;
            }

            if ($endsAt !== null && $endsAt < $now) {
                return false;
            }

            return true;
        }));
    }

    public function renderNotices(): void
    {
        foreach ($this->active() as $announcement) {
            $type = (string) ($announcement['type'] ?? 'info');
            $class = in_array($type, ['success', 'warning', 'error', 'info'], true) ? $type : 'info';

            printf(
                '<div class="notice notice-%1$s"><p><strong>%2$s</strong> %3$s</p></div>',
                esc_attr($class),
                esc_html((string) ($announcement['title'] ?? '')),
                esc_html((string) ($announcement['body'] ?? '')),
            );
        }
    }
}
