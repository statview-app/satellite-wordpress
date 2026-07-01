<?php

declare(strict_types=1);

/*
 * Configuration for the WordPress PHPUnit test suite.
 *
 * Values are read from the environment so the same file works inside the
 * wp-env "tests-cli" container (where these variables are provided) and in a
 * custom CI setup. Override any of them with the matching WORDPRESS_DB_* env
 * var if your database differs.
 */

// Path to the WordPress core checkout. Inside wp-env this is /var/www/html.
if (! defined('ABSPATH')) {
    define('ABSPATH', getenv('WP_CORE_DIR') ?: '/var/www/html/');
}

define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'tests-wordpress');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'password');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'mysql');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

$table_prefix = 'wptests_';

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Statview Satellite Tests');
define('WP_PHP_BINARY', 'php');
define('WP_DEBUG', true);
