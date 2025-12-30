<?php

namespace App\Filament\Panel\Admin\Resources\Users\Schemas;


use App\Models\Role;
use Detection\MobileDetect;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Components\UserFormComponent;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;

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
            ->disabled(fn($record) => $record ? true : false)
            //สั่งให้ disable ทั้งฟอร์ม
            ->components([
                Section::Make('ข้อมูลเอกสาร')
                    ->description('รวมข้อมูลที่พนักงาน หรือ ผู้สมัครอับโหลดและเก็บข้อมูลด้วย Ai')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Tabs::make('Tabs_doc')
                            ->tabs([
                                Tab::make('บัตรประชาชน')
                                    ->tabslug('idcard')
                                    ->schema(fn($record, $component) => [
                                        ...(new UserFormComponent())->idcardComponent($record, $component->getCustomSlug()),
                                        self::fileUploadComponent($component, $record),
                                    ]),
                                Tab::make('เรซูเม่')
                                    ->tabslug('resume')
                                    ->schema(fn($record, $component) => [
                                        (new UserFormComponent())->resumeComponent($record, $component->getCustomSlug()),
                                        self::fileUploadComponent($component, $record),
                                    ]),
                                Tab::make('วุฒิการศึกษา') //หลายเอกสาร
                                    ->tabslug('transcript')
                                    ->schema(fn($record, $component) => [
                                        (new UserFormComponent())->transcriptComponent($record, $component->getCustomSlug()),
                                        self::multipleFileUploadComponent($component, $record),
                                    ]),
                                Tab::make('ใบเกณฑ์ทหาร')
                                    ->tabslug('military')
                                    ->schema(fn($record, $component) => [
                                        (new UserFormComponent())->militaryComponent($record, $component->getCustomSlug()),
                                        self::fileUploadComponent($component, $record),
                                    ]),
                                Tab::make('ใบสถานะการสมรส')
                                    ->tabslug('marital')
                                    ->schema(fn($record, $component) => [
                                        (new UserFormComponent())->maritalComponent(),
                                        self::fileUploadComponent($component, $record),
                                    ]),
                                Tab::make('ใบ Certificate') //หลายเอกสาร
                                    ->tabslug('certificate')
                                    ->schema(fn($record, $component) => [
                                        (new UserFormComponent())->certificateComponent($record, $component->getCustomSlug()),
                                        self::multipleFileUploadComponent($component, $record),
                                    ]),
                                Tab::make('เอกสารเพิ่มเติม') //หลายเอกสาร
                                    ->tabslug('another')
                                    ->schema(fn($record, $component) => [
                                        (new UserFormComponent())->anotherDocComponent($record, $component->getCustomSlug()),
                                        self::multipleFileUploadComponent($component, $record),
                                    ]),

                            ])->columnSpanFull()->persistTabInQueryString()
                    ]),
                Section::Make('ข้อมูลเพิ่มเติม')
                    ->description('ข้อมูลเพิ่มเติมนอกเหนือจากการอับโหลดเอกสาร')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        Tabs::make('Tabs_additional')
                            ->tabs([
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
                            ])
                    ])


            ]);
    }

    private static function fileUploadComponent($component, $record): AdvancedFileUpload
    {
        return
            AdvancedFileUpload::make($component->getCustomSlug())
            ->removeUploadedFileButtonPosition('right')
            ->openable()
            ->downloadable()
            ->deletable(false)
            ->previewable(function ($state) {
                $name = basename($state);
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                return self::$isAndroidOS && $extension === 'pdf' ? 0 : 1;
            })
            ->label('เลือกไฟล์')
            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
            ->disk('public')
            ->directory('emp_files')
            ->afterStateHydrated(function ($set) use ($component, $record) {

                if (!blank($record)) {
                    $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                    $set($component->getCustomSlug(), $doc ? $doc->path : null);
                }
            })
            ->hidden(function ($record) use ($component) {
                if (!blank($record)) {
                    $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getCustomSlug())->first();
                    return $doc ? 0 : 1;
                }
            });
    }

    private static function multipleFileUploadComponent($component, $record): AdvancedFileUpload
    {
        return
            self::fileUploadComponent($component, $record)
            ->multiple()
            ->previewable(function ($state) {
                $docext = [];
                foreach ($state as $doc) {
                    $name = basename($doc);
                    $extension = pathinfo($name, PATHINFO_EXTENSION);
                    $docext[] = $extension;
                }
                return self::$isAndroidOS && in_array('pdf', $docext) ? 0 : 1;
            });
    }
}
