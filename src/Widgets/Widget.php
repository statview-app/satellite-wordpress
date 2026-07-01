<?php

declare(strict_types=1);

namespace Statview\Satellite\Widgets;

/**
 * A single-value widget. Fluent API mirrors the Laravel package's Widget so
 * the registration experience is familiar across both satellites.
 *
 *   Widget::make('total_posts')->title('Posts')->value(42)->description('...');
 */
class Widget
{
    protected ?string $title = null;

    protected mixed $value = null;

    protected ?string $description = null;

    protected string $type = 'stat';

    public function __construct(protected string $code) {}

    public static function make(string $code): static
    {
        return new static($code);
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function value(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'title' => $this->title,
            'value' => $this->value,
            'description' => $this->description,
            'type' => $this->type,
        ];
    }
}
