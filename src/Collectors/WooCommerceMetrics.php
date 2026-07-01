<?php

declare(strict_types=1);

namespace Statview\Satellite\Collectors;

use Statview\Satellite\Widgets\Widget;

/**
 * Default WooCommerce widgets. These are only produced when WooCommerce is
 * active, so the satellite degrades gracefully on stores and non-stores alike.
 */
final class WooCommerceMetrics
{
    public function isAvailable(): bool
    {
        return class_exists('WooCommerce') && function_exists('wc_get_orders');
    }

    /**
     * @return array<int,Widget>
     */
    public function widgets(): array
    {
        if (! $this->isAvailable()) {
            return [];
        }

        $today = (string) gmdate('Y-m-d 00:00:00');

        $ordersToday = wc_get_orders([
            'limit' => -1,
            'return' => 'ids',
            'date_created' => '>=' . $today,
        ]);

        $recentOrders = wc_get_orders([
            'limit' => -1,
            'status' => ['wc-completed', 'wc-processing'],
            'date_created' => '>=' . gmdate('Y-m-d 00:00:00', strtotime('-7 days')),
        ]);

        $revenue = 0.0;
        foreach ($recentOrders as $order) {
            $revenue += (float) $order->get_total();
        }

        return [
            Widget::make('woocommerce_orders_today')
                ->title('Orders today')
                ->value(is_array($ordersToday) ? count($ordersToday) : 0),

            Widget::make('woocommerce_revenue_7d')
                ->title('Revenue (7 days)')
                ->value(round($revenue, 2))
                ->description('Completed and processing orders in the last 7 days.'),
        ];
    }
}
