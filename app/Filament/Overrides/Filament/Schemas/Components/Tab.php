<?php

namespace App\Filament\Overrides\Filament\Schemas\Components;

use Closure;

use Filament\Schemas\Components\Tabs\Tab as BaseTab;
use Illuminate\Support\Str;

class Tab extends BaseTab
{
    

    // --- ส่วนที่เพิ่มใหม่ ---
    protected string | Closure | null $customSlug = null;

    public function tabSlug(string | Closure | null $slug): static
    {
        $this->customSlug = $slug;

        return $this;
    }

    public function getCustomSlug(): ?string
    {
        return $this->evaluate($this->customSlug);
    }
    // --- สิ้นสุดส่วนที่เพิ่มใหม่ ---

    protected function setUp(): void
    {
        parent::setUp();

        $this->key(function (Tab $component): string {
            $label = $this->getLabel();
            $statePath = $component->getStatePath();

            // --- การปรับ Logic ในการสร้าง Key ---
            // 1. ตรวจสอบว่ามี custom slug กำหนดไว้หรือไม่
            $slug = $this->getCustomSlug();

            if (filled($slug)) {
                // 2. ถ้ามี ให้ใช้ custom slug นั้นเลย
                $baseKey = $slug;
            } else {
                // 3. ถ้าไม่มี ให้ใช้ logic การสร้าง slug อัตโนมัติเดิม (จาก Label)
                $baseKey = Str::slug(Str::transliterate($label, strict: true));
            }

            // 4. ผนวกส่วนท้าย '::data::tab' เหมือนเดิม
            return $baseKey . '::' . (filled($statePath) ? "{$statePath}::tab" : 'tab');
            // --- สิ้นสุดการปรับ Logic ---
        }, isInheritable: false);
    }

}
