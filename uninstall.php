<?php

declare(strict_types=1);

// Triggered by WordPress when the plugin is uninstalled.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('statview_settings');
delete_transient('statview_announcements');
