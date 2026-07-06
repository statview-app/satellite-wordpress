<?php

declare(strict_types=1);

namespace Statview\Satellite\Services;

use Statview\Satellite\Config;
use Statview\Satellite\Http\Client;

/**
 * Posts timeline events to Statview.
 *
 * Targets POST /api/timeline/{project_id}, mirroring the Laravel package's
 * Statview::postToTimeline(). Accepted types and the default icon match the
 * server's validation contract.
 */
final class Timeline
{
    private const TYPES = ['default', 'info', 'success', 'warning', 'danger'];

    private const ICONS = [
        'default' => '📣',
        'info' => 'ℹ️',
        'success' => '✅',
        'warning' => '⚠️',
        'danger' => '🚨',
    ];

    public function __construct(
        private readonly Config $config,
        private readonly Client $client,
    ) {}

    /**
     * @param array<int,array{label:string,url:string,icon?:string}> $actions
     * @param array<int,string> $tags
     */
    public function post(string $title, string $body, string $type = 'default', ?string $icon = null, array $actions = [], array $tags = []): bool
    {
        if (! $this->config->isConfigured()) {
            return false;
        }

        if (! in_array($type, self::TYPES, true)) {
            $type = 'default';
        }

        $response = $this->client->post('timeline/' . $this->config->projectId(), [
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'icon' => $icon ?? self::ICONS[$type],
            'actions' => array_values($actions),
            'tags' => array_values($tags),
        ]);

        return $response !== null;
    }
}
