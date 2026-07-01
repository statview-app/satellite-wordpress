<?php

declare(strict_types=1);

namespace Statview\Satellite\Rest;

use Statview\Satellite\Collectors\EnvironmentCollector;
use WP_REST_Response;

/**
 * Returns the environment metadata. Shape matches the Laravel package's
 * /about endpoint: { "data": { "<Category>": { "<key>": <value> } } }.
 *
 * The Statview server uses array_keys($data) as "about_categories" and
 * flattens the nested map (Category.key) into individual meta widgets, so the
 * grouping by category drives how metadata is displayed.
 */
final class AboutController
{
    public function handle(): WP_REST_Response
    {
        return new WP_REST_Response([
            'data' => (new EnvironmentCollector())->collect(),
        ]);
    }
}
