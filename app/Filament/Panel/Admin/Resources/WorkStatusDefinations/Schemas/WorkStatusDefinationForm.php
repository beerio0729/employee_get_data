<?php

namespace App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use App\Filament\Panel\Admin\Components\Resources\Forms\WorkStatusDefinationFromComponent;


class WorkStatusDefinationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('กำหนดสถานะบุคคล')
                    ->columnSpanFull()
                    ->columns(3)
                    ->description('ใช้สำหรับระบุสถานะตัวตนของบุคคลนั้นว่าเป็นะไรในบริษัท เช่น เป็นพนักงาน, นักศึกษาฝึกงาน หรือ ผู้สมัคร')
                    ->schema([
                        ...WorkStatusDefinationFromComponent::formComponent(),
                        Select::make('main_work_status')
                            ->required()
                            ->label('เลือกกลุ่มสถานะ')
                            ->options(config("workstateconfig.main_work_status")),
                        
                    ])
            ]);
    }
}
