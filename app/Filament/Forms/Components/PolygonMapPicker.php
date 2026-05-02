<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class PolygonMapPicker extends Field
{
    protected string $view = 'filament.forms.components.polygon-map-picker';

    protected array $center = [18.4861, -69.9312];

    protected int $zoom = 13;

    protected int $height = 600;

    public function center(array $center): static
    {
        $this->center = $center;

        return $this;
    }

    public function zoom(int $zoom): static
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getCenter(): array
    {
        return $this->center;
    }

    public function getZoom(): int
    {
        return $this->zoom;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}
