<?php

declare(strict_types=1);

use Statview\Satellite\Config;
use Statview\Satellite\Http\Client;
use Statview\Satellite\Services\Timeline;
use Statview\Satellite\Widgets\Widget;

if (! function_exists('statview_post_to_timeline')) {
    /**
     * Post a message to the Statview timeline.
     *
     * @param array<int,array{label:string,url:string,icon?:string}> $actions
     */
    function statview_post_to_timeline(string $title, string $body, string $type = 'default', ?string $icon = null, array $actions = []): bool
    {
        $config = new Config();

        return (new Timeline($config, new Client($config)))->post($title, $body, $type, $icon, $actions);
    }
}

if (! function_exists('statview_register_widget')) {
    /**
     * Convenience helper to add a single widget via the statview_widgets filter.
     */
    function statview_register_widget(Widget $widget): void
    {
        add_filter('statview_widgets', static function (array $widgets) use ($widget): array {
            $widgets[] = $widget;

            return $widgets;
        });
    }
}
