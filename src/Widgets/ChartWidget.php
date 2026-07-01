<?php

declare(strict_types=1);

namespace Statview\Satellite\Widgets;

/**
 * A widget that carries a series of data points instead of a single value.
 *
 *   ChartWidget::make('signups')->title('Signups')->type('line')->data([
 *       ['label' => 'Jan', 'value' => 12],
 *   ]);
 */
final class ChartWidget extends Widget
{
    protected string $type = 'line';

    /** @var array<int,array{label:string,value:mixed}> */
    protected array $data = [];

    public function type(string $type = 'line'): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param array<int,array{label:string,value:mixed}> $data
     */
    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'data' => $this->data,
        ]);
    }
}
