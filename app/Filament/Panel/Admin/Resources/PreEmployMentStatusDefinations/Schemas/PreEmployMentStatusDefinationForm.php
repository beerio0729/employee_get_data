<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
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
            ->columns(3)
            ->description('ใช้สำหรับระบุสถานะตามเหตุการณ์ของผู้สมัคร เช่น เอกสารผ่านแล้ว นัดสัมภาษณ์แล้ว เป็นต้น')
            ->schema([
                ...WorkStatusDefinationFromComponent::formComponent(),
                Select::make('work_phase')
                    ->required()
                    ->label('เลือกช่วงเหตุการณ์')
                    ->options(config("workstateconfig.pre_employment_phase_state")),
                Hidden::make('work_status_def_id')
                    ->default(1),
                    
            ]);
    }
}
