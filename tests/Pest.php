<?php

declare(strict_types=1);

use Brain\Monkey;

require_once __DIR__ . '/stubs.php';

uses()
    ->beforeEach(function (): void {
        Monkey\setUp();
    })
    ->afterEach(function (): void {
        Monkey\tearDown();
    })
    ->in('Unit');

// The integration suite runs against a real WordPress instance booted by
// tests/bootstrap.php, so WP_UnitTestCase only exists during that run.
if (class_exists('WP_UnitTestCase')) {
    uses(WP_UnitTestCase::class)->in('Integration');
}
