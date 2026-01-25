<?php

namespace App\Filament\Overrides\Panel;

use Closure;
use Filament\Panel;
use Filament\Support\Enums\Width;

class OverridePanel extends Panel //ที่ต้องทำเพราะ maxContentWidth มันใส่ function ไม่ได้ เลยต้องมา override
{
    protected ?Closure $dynamicMaxContentWidth = null;

    public function maxContentWidth(
        Width | string | Closure | null $width
    ): static {
        if ($width instanceof Closure) {
            $this->dynamicMaxContentWidth = $width;

            return $this;
        }

        return parent::maxContentWidth($width);
    }

    public function getMaxContentWidth(): Width | string | null
    {
        if ($this->dynamicMaxContentWidth) {
            /** @var Width|string|null $width */
            $width = value($this->dynamicMaxContentWidth);

            return $width;
        }

        return parent::getMaxContentWidth();
    }
}
