<?php

namespace App\Filament\Panel\Admin\Resources\OpenPositions;

use App\Filament\Panel\Admin\Resources\OpenPositions\Pages\CreateOpenPosition;
use App\Filament\Panel\Admin\Resources\OpenPositions\Pages\EditOpenPosition;
use App\Filament\Panel\Admin\Resources\OpenPositions\Pages\ListOpenPositions;
use App\Filament\Panel\Admin\Resources\OpenPositions\Schemas\OpenPositionForm;
use App\Filament\Panel\Admin\Resources\OpenPositions\Tables\OpenPositionsTable;
use App\Models\OpenPosition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpenPositionResource extends Resource
{
    protected static ?string $model = OpenPosition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;

    protected static ?string $modelLabel = 'ตำแหน่งที่เปิดรับสมัคร';

    protected static ?string $navigationLabel = 'ตำแหน่งที่เปิดรับสมัคร';

    protected static ?string $recordTitleAttribute = 'name_th';

    public static function form(Schema $schema): Schema
    {
        return OpenPositionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpenPositionsTable::configure($table);
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
            'index' => ListOpenPositions::route('/'),
            'create' => CreateOpenPosition::route('/create'),
            'edit' => EditOpenPosition::route('/{record}/edit'),
        ];
    }
}
