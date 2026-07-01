<?php

declare(strict_types=1);

namespace Statview\Satellite\Rest;

use Statview\Satellite\Widgets\WidgetRegistry;
use WP_REST_Response;

/**
 * Returns the registered widgets. Shape matches the Laravel package's
 * /stats endpoint: { "widgets": [ {code,title,value,description,type,...} ] }.
 */
final class StatsController
{
    public function handle(): WP_REST_Response
    {
        return new WP_REST_Response([
            'widgets' => (new WidgetRegistry())->toArray(),
        ]);
    }
}
