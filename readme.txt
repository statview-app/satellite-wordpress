=== Statview Satellite ===
Contributors: statview
Tags: monitoring, statistics, stats, statview, analytics
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT

Sets up the communication channel between your WordPress site and Statview for monitoring, stats and announcements.

== Description ==

Statview Satellite connects your WordPress site to your Statview project. Once configured with the DSN from your Statview panel, Statview polls your site for environment metadata and metrics, and you can post timeline events and show announcements.

Features:

* Exposes environment metadata (PHP/WordPress versions, theme, active plugins, drivers) to Statview.
* Reports core content metrics (posts, pages, comments, users) and pending updates.
* WooCommerce metrics (orders, revenue) when WooCommerce is active.
* Extensible widgets through the `statview_widgets` filter.
* Post to your Statview timeline with `statview_post_to_timeline()`.
* Shows Statview announcements as admin notices.

== Installation ==

1. Upload and activate the plugin.
2. Go to Settings → Statview and paste the DSN shown during project setup in your Statview panel.
3. In Statview, set the project path to `wp-json/statview/v1`.

== Usage ==

Register a custom widget:

`
add_filter('statview_widgets', function (array $widgets) {
    $widgets[] = \Statview\Satellite\Widgets\Widget::make('newsletter_subscribers')
        ->title('Subscribers')
        ->value(get_option('subscriber_count', 0));

    return $widgets;
});
`

Post a timeline event:

`
statview_post_to_timeline('Backup complete', 'Nightly backup finished.', 'success');
`

== Changelog ==

= 1.0.0 =
* Initial release.
