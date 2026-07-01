<?php

declare(strict_types=1);

use Statview\Satellite\Widgets\ChartWidget;
use Statview\Satellite\Widgets\Widget;

it('builds a stat widget array matching the satellite contract', function (): void {
    $widget = Widget::make('total_posts')
        ->title('Posts')
        ->value(42)
        ->description('All posts');

    expect($widget->toArray())->toBe([
        'code' => 'total_posts',
        'title' => 'Posts',
        'value' => 42,
        'description' => 'All posts',
        'type' => 'stat',
    ]);
});

it('builds a chart widget with a default line type and data', function (): void {
    $widget = ChartWidget::make('signups')
        ->title('Signups')
        ->data([
            ['label' => 'Jan', 'value' => 12],
            ['label' => 'Feb', 'value' => 30],
        ]);

    $array = $widget->toArray();

    expect($array['type'])->toBe('line')
        ->and($array['code'])->toBe('signups')
        ->and($array['data'])->toHaveCount(2)
        ->and($array['data'][0])->toBe(['label' => 'Jan', 'value' => 12]);
});

it('allows overriding the chart type', function (): void {
    $widget = ChartWidget::make('sales')->type('bar')->data([]);

    expect($widget->toArray()['type'])->toBe('bar');
});
