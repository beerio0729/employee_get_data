<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentGrades;

use BackedEnum;
use UnitEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Models\WorkStatus\PostEmploymentGrade;
use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Pages\EditPostEmploymentGrade;
use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Pages\ListPostEmploymentGrades;
use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Pages\CreatePostEmploymentGrade;
use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Schemas\PostEmploymentGradeForm;
use App\Filament\Panel\Admin\Resources\PostEmploymentGrades\Tables\PostEmploymentGradesTable;

class PostEmploymentGradeResource extends Resource
{
    protected static ?string $model = PostEmploymentGrade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Trophy;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 14;

    protected static ?string $recordTitleAttribute = 'name_th';

    protected static ?string $modelLabel = 'ระดับพนักงาน';

    protected static ?string $navigationLabel = 'ระดับพนักงาน';
    
    protected static ?string $slug = 'position_grade';

    public static function form(Schema $schema): Schema
    {
        return PostEmploymentGradeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostEmploymentGradesTable::configure($table);
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
            'index' => ListPostEmploymentGrades::route('/'),
            'create' => CreatePostEmploymentGrade::route('/create'),
            'edit' => EditPostEmploymentGrade::route('/{record}/edit'),
        ];
    }
}
