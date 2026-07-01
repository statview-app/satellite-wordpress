<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Statview\Satellite\Rest\StatsController;

beforeEach(function (): void {
    Functions\when('wp_count_posts')->justReturn((object) ['publish' => 1]);
    Functions\when('wp_count_comments')->justReturn((object) ['approved' => 0, 'moderated' => 0]);
    Functions\when('count_users')->justReturn(['total_users' => 1]);
    Functions\when('wp_get_update_data')->justReturn(['counts' => ['total' => 0]]);
    Functions\when('apply_filters')->returnArg(2);
});

it('wraps the widgets under a "widgets" key like the satellite contract', function (): void {
    $response = (new StatsController())->handle();
    $data = $response->get_data();

    expect($data)->toHaveKey('widgets')
        ->and($data['widgets'])->toBeArray()
        ->and($data['widgets'][0])->toHaveKeys(['code', 'title', 'value', 'type']);
});
