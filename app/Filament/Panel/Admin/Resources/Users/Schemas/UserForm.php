<?php

namespace App\Filament\Panel\Admin\Resources\Users\Schemas;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Districts;
use App\Models\Provinces;
use Detection\MobileDetect;
use Illuminate\Support\Str;
use App\Models\Subdistricts;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use App\Filament\Components\UserFormComponent;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UserForm
{
    public static bool $isMobile;
    public static bool $isAndroidOS;

    public static function configure(Schema $schema): Schema
    {
        $detect = new MobileDetect();
        self::$isMobile = $detect->isMobile();
        self::$isAndroidOS = $detect->isAndroidOS();
        $currentYear_BE = date('Y') + 543; // เช่น พ.ศ. 2025 + 543 = 2568
        $years_education_BE = range($currentYear_BE - 30, $currentYear_BE); // 40 ปีย้อนหลัง

        $currentYear_AD = date('Y'); // เช่น ค.ศ. 2025
        $years_education_AD = range($currentYear_AD - 30, $currentYear_AD); // 40 ปีย้อนหลัง
        return $schema
            ->disabled()
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('บัตรประชาชน')
                            ->tabslug('idcard')
                            ->schema(fn($record, $component) => [
                                ...(new UserFormComponent())->idcardComponent($record, $component->getCustomSlug()),
                                AdvancedFileUpload::make($component->getCustomSlug())
                                    ->removeUploadedFileButtonPosition('right')
                                    ->openable()
                                    ->previewable(function ($state) {
                                        $name = basename($state);
                                        $extension = pathinfo($name, PATHINFO_EXTENSION);
                                        return self::$isAndroidOS && $extension === 'pdf' ? 0 : 1;
                                    })
                                    ->label('เลือกไฟล์')
                                    ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($record, $set) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        $set($component->getCustomSlug(), $doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($record) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),
                        Tab::make('เรซูเม่')
                            ->tabslug('resume')
                            ->schema(fn($record, $component) => [
                                (new UserFormComponent())->resumeComponent($record, $component->getCustomSlug()),
                                AdvancedFileUpload::make($component->getCustomSlug())
                                    ->label('เลือกไฟล์')
                                    ->openable()
                                    ->disabled()
                                    ->previewable(function ($state) {
                                        $name = basename($state);
                                        $extension = pathinfo($name, PATHINFO_EXTENSION);
                                        return self::$isAndroidOS && $extension === 'pdf' ? 0 : 1;
                                    })
                                    ->downloadable()
                                    ->deletable(false)
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($record, $set) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        $set($component->getCustomSlug(), $doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($record) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),
                        Tab::make('วุฒิการศึกษา') //หลายเอกสาร
                            ->tabslug('transcript')
                            ->schema(fn($record,$component) => [
                                (new UserFormComponent())->transcriptComponent($record, $component->getCustomSlug()),
                                AdvancedFileUpload::make($component->getCustomSlug())
                                    ->label('เลือกไฟล์')
                                    ->multiple()
                                    ->appendFiles()
                                    ->openable()
                                    ->disabled()
                                    ->previewable(function ($state) {
                                        $docext = [];
                                        foreach ($state as $doc) {
                                            $name = basename($doc);
                                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                                            $docext[] = $extension;
                                        }
                                        return self::$isAndroidOS && in_array('pdf', $docext) ? 0 : 1;
                                    })
                                    ->downloadable()
                                    ->deletable(false)
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($record, $set) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        $set($component->getCustomSlug(), $doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($record) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),
                        Tab::make('ใบเกณฑ์ทหาร')
                            ->tabslug('military')
                            ->schema(fn($record, $component) => [
                                (new UserFormComponent())->militaryComponent($record, $component->getCustomSlug()),
                                AdvancedFileUpload::make($component->getCustomSlug())
                                    ->label('เลือกไฟล์')
                                    ->openable()
                                    ->disabled()
                                    ->previewable(function ($state) {
                                        $name = basename($state);
                                        $extension = pathinfo($name, PATHINFO_EXTENSION);
                                        return self::$isAndroidOS && $extension === 'pdf' ? 0 : 1;
                                    })
                                    ->downloadable()
                                    ->deletable(false)
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($record, $set) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        $set($component->getCustomSlug(), $doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($record) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),
                        Tab::make('ใบสถานะการสมรส')
                            ->tabslug('marital')
                            ->schema(fn($component) => [
                                (new UserFormComponent())->maritalComponent(),
                                AdvancedFileUpload::make($component->getCustomSlug())
                                    ->label('เลือกไฟล์')
                                    ->openable()
                                    ->disabled()
                                    ->previewable(function ($state) {
                                        $name = basename($state);
                                        $extension = pathinfo($name, PATHINFO_EXTENSION);
                                        return self::$isAndroidOS && $extension === 'pdf' ? 0 : 1;
                                    })
                                    ->downloadable()
                                    ->deletable(false)
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($record, $set) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        $set($component->getCustomSlug(), $doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($record) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),
                        Tab::make('ใบ Certificate') //หลายเอกสาร
                            ->tabslug('certificate')
                            ->schema(fn($record, $component) => [
                                (new UserFormComponent())->certificateComponent($record, $component->getCustomSlug()),
                                AdvancedFileUpload::make($component->getCustomSlug())
                                    ->label('เลือกไฟล์')
                                    ->multiple()
                                    ->appendFiles()
                                    ->openable()
                                    ->disabled()
                                    ->previewable(function ($state) {
                                        $docext = [];
                                        foreach ($state as $doc) {
                                            $name = basename($doc);
                                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                                            $docext[] = $extension;
                                        }
                                        return self::$isAndroidOS && in_array('pdf', $docext) ? 0 : 1;
                                    })
                                    ->downloadable()
                                    ->deletable(false)
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($record, $set) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        $set($component->getCustomSlug(), $doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($record) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),
                        Tab::make('เอกสารเพิ่มเติม') //หลายเอกสาร
                            ->tabslug('another')
                            ->schema(fn($record, $component) => [
                                (new UserFormComponent())->anotherDocComponent($record, $component->getCustomSlug()),
                                AdvancedFileUpload::make($component->getCustomSlug())
                                    ->label('เลือกไฟล์')
                                    ->multiple()
                                    ->appendFiles()
                                    ->openable()
                                    ->disabled()
                                    ->previewable(function ($state) {
                                        $docext = [];
                                        foreach ($state as $doc) {
                                            $name = basename($doc);
                                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                                            $docext[] = $extension;
                                        }
                                        return self::$isAndroidOS && in_array('pdf', $docext) ? 0 : 1;
                                    })
                                    ->downloadable()
                                    ->deletable(false)
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($record, $set) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        $set($component->getCustomSlug(), $doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($record) use ($component) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),
                        Tab::make('ข้อมูลครอบครัว')
                            ->extraAttributes(
                                fn() => (self::$isMobile)
                                    ? ['style' => 'padding: 24px 15px']
                                    : []
                            )
                            ->schema(
                                function () {
                                    return [...(new UserFormComponent())->familyComponent()];
                                }
                            ),
                        Tab::make('ข้อมูลผู้ที่ติดต่อได้ยามฉุกเฉิน')
                            ->extraAttributes(
                                fn() => (self::$isMobile)
                                    ? ['style' => 'padding-right: 12px; padding-left: 12px;']
                                    : []
                            )
                            ->schema(
                                function () {
                                    return [(new UserFormComponent())->emergencyContactComponent()];
                                }
                            ),
                        Tab::make('ข้อมูลสุขภาพ')
                            ->extraAttributes(
                                fn() => (self::$isMobile)
                                    ? ['style' => 'padding-right: 12px; padding-left: 12px;']
                                    : []
                            )
                            ->schema(
                                function () {
                                    return [(new UserFormComponent())->healthInfoComponent()];
                                }
                            ),

                        Tab::make('คำถามเพิ่มเติม')
                            ->extraAttributes(
                                fn() => (self::$isMobile)
                                    ? ['style' => 'padding-right: 12px; padding-left: 12px;']
                                    : []
                            )
                            ->schema(
                                function () {
                                    return [(new UserFormComponent())->additionalComponent()];
                                }
                            ),
                    ])->columnSpanFull()->persistTabInQueryString()

            ]);
    }
}
