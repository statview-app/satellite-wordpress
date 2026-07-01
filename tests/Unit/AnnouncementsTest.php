<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Statview\Satellite\Config;
use Statview\Satellite\Services\Announcements;

function announcementsConfig(): Config
{
    Functions\when('get_option')->justReturn([
        'dsn' => '',
        'endpoint' => 'https://statview.app',
        'project_id' => '1',
        'api_key' => 'secret',
    ]);

    return new Config();
}

it('returns announcements that are within their active window', function (): void {
    Functions\when('get_transient')->justReturn([
        ['title' => 'Live now', 'starts_at' => gmdate('c', time() - 3600), 'ends_at' => gmdate('c', time() + 3600)],
        ['title' => 'No window'],
    ]);

    $active = (new Announcements(announcementsConfig()))->active();

    expect($active)->toHaveCount(2);
});

it('filters out announcements that have not started yet', function (): void {
    Functions\when('get_transient')->justReturn([
        ['title' => 'Future', 'starts_at' => gmdate('c', time() + 86400)],
    ]);

    expect((new Announcements(announcementsConfig()))->active())->toBeEmpty();
});

it('filters out announcements that have already ended', function (): void {
    Functions\when('get_transient')->justReturn([
        ['title' => 'Expired', 'ends_at' => gmdate('c', time() - 86400)],
    ]);

    expect((new Announcements(announcementsConfig()))->active())->toBeEmpty();
});

it('returns an empty list when no announcements are cached', function (): void {
    Functions\when('get_transient')->justReturn(false);

    expect((new Announcements(announcementsConfig()))->active())->toBe([]);
});
