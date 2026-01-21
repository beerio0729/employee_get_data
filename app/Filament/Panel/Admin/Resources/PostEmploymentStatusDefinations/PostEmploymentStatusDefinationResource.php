<?php

namespace App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Detection\MobileDetect;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;
use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Pages\EditPostEmploymentStatusDefination;
use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Pages\ListPostEmploymentStatusDefinations;
use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Pages\CreatePostEmploymentStatusDefination;
use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Schemas\PostEmploymentStatusDefinationForm;
use App\Filament\Panel\Admin\Resources\PostEmploymentStatusDefinations\Tables\PostEmploymentStatusDefinationsTable;

class PostEmploymentStatusDefinationResource extends Resource
{
    protected static ?string $model = WorkStatusDefinationDetail::class;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name_th';

    protected static ?string $modelLabel = 'สถานะบุคคลากรหลังอนุมัติจ้างงาน';

    protected static ?string $navigationLabel = 'สถานะบุคคลากรหลังอนุมัติจ้างงาน';

    public static function getNavigationIcon(): ?Heroicon
    {
        $detect = new MobileDetect();
        if (($detect->isiOS() || $detect->isAndroidOS()) || !Cache::get('top_navigation_' . auth()->id()) ?? 0) {
            return null;
        } else {
            return Heroicon::EllipsisVertical;
        }
    }
    
    public static function getNavigationParentItem(): ?string
    {   
        $detect = new MobileDetect();
        if (($detect->isiOS() || $detect->isAndroidOS()) || !Cache::get('top_navigation_' . auth()->id()) ?? 0) {
            return null;
        } else {
            return 'ประเภทบุคคลากร';
        }
    }

    public static function form(Schema $schema): Schema
    {
        return PostEmploymentStatusDefinationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostEmploymentStatusDefinationsTable::configure($table);
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
            'index' => ListPostEmploymentStatusDefinations::route('/'),
            'create' => CreatePostEmploymentStatusDefination::route('/create'),
            'edit' => EditPostEmploymentStatusDefination::route('/{record}/edit'),
        ];
    }
}
