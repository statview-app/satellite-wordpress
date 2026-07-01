<?php

declare(strict_types=1);

namespace Statview\Satellite\Collectors;

use Statview\Satellite\Widgets\Widget;

/**
 * Default widgets describing pending updates — a useful signal for managed
 * WordPress sites. Update counts are read from WordPress' own update cache via
 * wp_get_update_data(), so they reflect what the dashboard shows.
 */
final class UpdatesHealth
{
    /**
     * @return array<int,Widget>
     */
    public function widgets(): array
    {
        $data = function_exists('wp_get_update_data') ? wp_get_update_data() : ['counts' => []];
        $counts = is_array($data) && isset($data['counts']) && is_array($data['counts']) ? $data['counts'] : [];

        return [
            Widget::make('wp_updates_total')
                ->title('Pending updates')
                ->value((int) ($counts['total'] ?? 0))
                ->description('Core, plugin, theme and translation updates available.'),

            Widget::make('wp_updates_core')
                ->title('Core updates')
                ->value((int) ($counts['wordpress'] ?? 0)),

            Widget::make('wp_updates_plugins')
                ->title('Plugin updates')
                ->value((int) ($counts['plugins'] ?? 0)),

            Widget::make('wp_updates_themes')
                ->title('Theme updates')
                ->value((int) ($counts['themes'] ?? 0)),
        ];
    }
}
