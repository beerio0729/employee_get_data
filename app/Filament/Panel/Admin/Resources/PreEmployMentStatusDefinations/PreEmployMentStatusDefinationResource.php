<?php

namespace App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Detection\MobileDetect;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;

use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages\EditPreEmployMentStatusDefination;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages\ListPreEmployMentStatusDefinations;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Pages\CreatePreEmployMentStatusDefination;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Schemas\PreEmployMentStatusDefinationForm;
use App\Filament\Panel\Admin\Resources\PreEmployMentStatusDefinations\Tables\PreEmployMentStatusDefinationsTable;

class PreEmployMentStatusDefinationResource extends Resource
{
    protected static ?string $model = WorkStatusDefinationDetail::class;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 9;

    protected static ?string $slug = 'pre_employment_def';

    protected static ?string $recordTitleAttribute = 'name_th';

    protected static ?string $modelLabel = 'สถานะบุคคลากรก่อนจ้างงาน';

    protected static ?string $navigationLabel = 'สถานะบุคคลากรก่อนจ้างงาน';
    
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
