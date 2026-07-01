<?php

declare(strict_types=1);

namespace Statview\Satellite\Widgets;

use Statview\Satellite\Collectors\CoreMetrics;
use Statview\Satellite\Collectors\UpdatesHealth;
use Statview\Satellite\Collectors\WooCommerceMetrics;

/**
 * Assembles the widget list returned on /stats.
 *
 * Default widgets come from the built-in collectors. Third-party plugins and
 * themes extend the list through the `statview_widgets` filter — the WordPress
 * equivalent of the Laravel package's Statview::registerWidgets() closure.
 */
final class WidgetRegistry
{
    /**
     * @return array<int,Widget>
     */
    public function all(): array
    {
        $widgets = array_merge(
            (new CoreMetrics())->widgets(),
            (new UpdatesHealth())->widgets(),
            (new WooCommerceMetrics())->widgets(),
        );

        /**
         * Filter the widgets exposed to Statview.
         *
         * @param array<int,Widget> $widgets
         */
        $widgets = apply_filters('statview_widgets', $widgets);

        return array_values(array_filter(
            is_array($widgets) ? $widgets : [],
            static fn ($widget): bool => $widget instanceof Widget,
        ));
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn (Widget $widget): array => $widget->toArray(), $this->all());
    }
}
