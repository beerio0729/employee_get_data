<?php

namespace App\Filament\Pages;

use Dom\Text;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Districts;
use App\Models\Provinces;
use Detection\MobileDetect;
use Illuminate\Support\Str;
use App\Models\Subdistricts;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Jobs\ProcessEmpDocJob;
use Filament\Facades\Filament;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use App\Jobs\ProcessNoJsonEmpDocJob;
use Filament\Forms\Components\Field;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditProfile extends BaseEditProfile
{
    
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::auth/pages/edit-profile.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->afterLabel([
                Icon::make(Heroicon::Star),
                __('filament-panels::auth/pages/edit-profile.form.name.afterlabel')
            ]);
    }

    protected function getTelComponent(): Component
    {
        return
            TextInput::make('tel')
            ->columnSpan(1)
            ->mask('9999999999')
            ->label(__('filament-panels::auth/pages/edit-profile.form.tel.label'))
            ->tel()
            ->required()
            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/');
    }


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ข้อมูลโปรไฟล์พื้นฐานของคุณ')
                    ->description('จำเป็นต้องกรอกข้อมูลให้ครบถ้วน หากไม่กรอกข้อมูลจะไม่สามารถอับโหลดเอกสารได้')
                    ->hiddenLabel()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getTelComponent(),
                    ])->columns(3),
                Section::make('แก้ไข้รหัสผ่าน')
                    ->description('คุณสามารถแก้ไขรหัสผ่านได้จากที่นี่ (ในกรณีที่การ Login ด้วย line มีปัญหา)')
                    ->hiddenLabel()
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])->columns(3)->collapsed(),
                //$this->getDataResume(),
            ]);
    }
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.index';
    }

    protected function getRedirectUrl(): ?string
    {
        return '/';
    }
    
}
