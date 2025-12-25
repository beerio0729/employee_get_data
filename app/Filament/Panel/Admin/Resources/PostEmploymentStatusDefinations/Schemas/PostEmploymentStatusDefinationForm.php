<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use App\Models\WorkStatusDefination\WorkStatusDefination;
use App\Filament\Panel\Admin\Components\Resources\Forms\WorkStatusDefinationFromComponent;

class PostEmploymentStatusDefinationForm
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
            ->description('ใช้สำหรับระบุสถานะย่อยของ สถานะบุคคลนั้นเช่น ถ้าเป็นพนักงานแล้ว มีสถานะอะไรบ้างเป็นต้น')
            ->schema([
                ...WorkStatusDefinationFromComponent::formComponent(),
                Select::make('work_status_def_id')
                    ->label('เลือกสถานะบุคคล')
                    ->required()
                    ->options(
                        fn() => WorkStatusDefination::where('main_work_status', 'post_employment')
                            ->pluck('name_th', 'id')
                    ),

            ]);
    }
}
