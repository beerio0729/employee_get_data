<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages\EditPreEmployMentStatusDefination;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages\ListPreEmployMentStatusDefinations;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages\CreatePreEmployMentStatusDefination;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Schemas\PreEmployMentStatusDefinationForm;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Tables\PreEmployMentStatusDefinationsTable;

class PreEmployMentStatusDefinationResource extends Resource
{
    protected static ?string $model = WorkStatusDefinationDetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $slug = 'pre_employment_def';

    protected static ?string $recordTitleAttribute = 'สถานะก่อนจ้างงาน';
    
    protected static ?string $modelLabel = 'สถานะก่อนจ้างงาน';

    protected static ?string $navigationLabel = 'กำหนดสถานะก่อนจ้างงาน';

    public static function form(Schema $schema): Schema
    {
        return PreEmployMentStatusDefinationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PreEmployMentStatusDefinationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPreEmployMentStatusDefinations::route('/'),
            'create' => CreatePreEmployMentStatusDefination::route('/create'),
            'edit' => EditPreEmployMentStatusDefination::route('/{record}/edit'),
        ];
    }
}
