<?php

namespace App\Filament\Overrides\Filament\Widgets\StatsOverviewWidget;

use Filament\Widgets\StatsOverviewWidget\Stat as BaseStat;

class Stat extends BaseStat
{
    protected ?int $progress = null;

    public function progress(int $percent): static
    {
        $this->progress = $percent;

        return $this;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }
}

    