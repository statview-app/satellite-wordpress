<?php

declare(strict_types=1);

namespace Statview\Satellite\Collectors;

use Statview\Satellite\Widgets\Widget;

/**
 * Default widgets describing core WordPress content volumes.
 */
final class CoreMetrics
{
    /**
     * @return array<int,Widget>
     */
    public function widgets(): array
    {
        $posts = wp_count_posts('post');
        $pages = wp_count_posts('page');
        $comments = wp_count_comments();
        $users = count_users();

        return [
            Widget::make('wp_published_posts')
                ->title('Published posts')
                ->value((int) ($posts->publish ?? 0))
                ->description('Posts with the published status.'),

            Widget::make('wp_published_pages')
                ->title('Published pages')
                ->value((int) ($pages->publish ?? 0)),

            Widget::make('wp_approved_comments')
                ->title('Approved comments')
                ->value((int) ($comments->approved ?? 0)),

            Widget::make('wp_pending_comments')
                ->title('Pending comments')
                ->value((int) ($comments->moderated ?? 0))
                ->description('Comments awaiting moderation.'),

            Widget::make('wp_total_users')
                ->title('Users')
                ->value((int) ($users['total_users'] ?? 0)),
        ];
    }
}
