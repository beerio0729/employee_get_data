<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use App\Models\WorkStatusDefination\WorkStatusDefination;
use App\Filament\Panel\Admin\Components\Resources\Forms\WorkStatusDefinationFromComponent;

class PreEmployMentStatusDefinationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::formComponent(),
            ]);
    }

    public static function formComponent(): Section
    {
        return Section::make('กำหนดสถานะบุคคล')
            ->columnSpanFull()
            ->columns(4)
            ->description('ใช้สำหรับระบุสถานะตามเหตุการณ์ของผู้สมัคร เช่น เอกสารผ่านแล้ว นัดสัมภาษณ์แล้ว เป็นต้น')
            ->schema([
                ...WorkStatusDefinationFromComponent::formForPreComponent('pre_employment'), 
            ]);
    }
    
    
}
