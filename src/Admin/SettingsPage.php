<?php

declare(strict_types=1);

namespace Statview\Satellite\Admin;

use Statview\Satellite\Config;

/**
 * "Settings → Statview" admin screen.
 *
 * Exposes a single DSN field (scheme://host/{project_id}/{api_key}); on save
 * the DSN is parsed into its components and the resolved endpoint/project are
 * shown read-only so the user can confirm the connection details.
 */
final class SettingsPage
{
    private const PAGE = 'statview-satellite';

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addMenu(): void
    {
        add_options_page(
            'Statview Satellite',
            'Statview',
            'manage_options',
            self::PAGE,
            [$this, 'render'],
        );
    }

    public function registerSettings(): void
    {
        register_setting(self::PAGE, Config::OPTION, [
            'type' => 'array',
            'sanitize_callback' => [Config::class, 'sanitize'],
            'default' => [],
        ]);
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $config = new Config();
        ?>
        <div class="wrap">
            <h1>Statview Satellite</h1>
            <form action="options.php" method="post">
                <?php settings_fields(self::PAGE); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="statview_dsn">DSN</label></th>
                        <td>
                            <input name="<?php echo esc_attr(Config::OPTION); ?>[dsn]"
                                   id="statview_dsn"
                                   type="text"
                                   class="regular-text code"
                                   value="<?php echo esc_attr($config->dsn()); ?>"
                                   placeholder="https://statview.app/{project_id}/{api_key}" />
                            <p class="description">
                                <?php esc_html_e('Paste the DSN shown during project setup in your Statview panel.', 'statview-satellite'); ?>
                            </p>
                        </td>
                    </tr>
                    <?php if ($config->isConfigured()) : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('Endpoint', 'statview-satellite'); ?></th>
                            <td><code><?php echo esc_html($config->endpoint()); ?></code></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Project ID', 'statview-satellite'); ?></th>
                            <td><code><?php echo esc_html($config->projectId()); ?></code></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Satellite path', 'statview-satellite'); ?></th>
                            <td><code><?php echo esc_html(rest_url('statview/v1')); ?></code></td>
                        </tr>
                    <?php endif; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
