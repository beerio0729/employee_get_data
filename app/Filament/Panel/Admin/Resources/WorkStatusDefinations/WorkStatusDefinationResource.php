<?php

namespace App\Filament\Panel\Admin\Resources\WorkStatusDefinations;

use BackedEnum;
use UnitEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

use App\Models\WorkStatusDefination\WorkStatusDefination;
use App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Pages\EditWorkStatusDefination;
use App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Pages\ListWorkStatusDefinations;
use App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Pages\CreateWorkStatusDefination;
use App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Schemas\WorkStatusDefinationForm;
use App\Filament\Panel\Admin\Resources\WorkStatusDefinations\Tables\WorkStatusDefinationsTable;

class WorkStatusDefinationResource extends Resource
{
    protected static ?string $model = WorkStatusDefination::class;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    protected static ?string $recordTitleAttribute = 'สถานะบุคคล';

    protected static ?string $modelLabel = 'สถานะบุคคล';

    protected static ?string $navigationLabel = 'กำหนดสถานะบุคคล';

    public static function form(Schema $schema): Schema
    {
        return WorkStatusDefinationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkStatusDefinationsTable::configure($table);
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
            'index' => ListWorkStatusDefinations::route('/'),
            'create' => CreateWorkStatusDefination::route('/create'),
            'edit' => EditWorkStatusDefination::route('/{record}/edit'),
        ];
    }
}
