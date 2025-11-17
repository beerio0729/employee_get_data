<?php

namespace App\Filament\Resources\Users\Schemas;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Districts;
use App\Models\Provinces;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear_BE = date('Y') + 543; // เช่น พ.ศ. 2025 + 543 = 2568
        $years_education_BE = range($currentYear_BE - 30, $currentYear_BE); // 40 ปีย้อนหลัง

        $currentYear_AD = date('Y'); // เช่น ค.ศ. 2025
        $years_education_AD = range($currentYear_AD - 30, $currentYear_AD); // 40 ปีย้อนหลัง
        return $schema
            ->components([
                Section::make('อีเมลที่คุณใช้งาน')
                    ->hiddenLabel()
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->readOnly(),
                        Select::make('role_id')
                            ->disabled()
                            ->label('สถานะ')
                            ->options(function(){
                                return Role::where('active', 1)->pluck('name', 'id');
                            }),
                    ])->columnSpanFull()->collapsible()->columns(2),
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Resume')
                            ->tabslug('resume')
                            ->schema([
                                FileUpload::make('image_profile')
                                    ->label('กรุณาอับโหลดรูปภาพ')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->disabled()
                                    ->openable()
                                    ->deletable(false)
                                    ->panelLayout('grid')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        return $doc ? 0 : 1;
                                    })
                                    ->image(),

                                Section::make('ข้อมูลทั่วไป')
                                    ->columns(3)
                                    ->relationship('userHasoneResume')
                                    ->schema([
                                        Select::make('prefix_name')
                                            ->hiddenlabel()
                                            ->placeholder('คำนำหน้าชื่อ')
                                            ->options(config("iconf.prefix_name"))
                                            ->disabled(),

                                        TextInput::make('name')
                                            ->hiddenlabel()
                                            ->placeholder('ชื่อ')
                                            ->readOnly(),

                                        TextInput::make('last_name')
                                            ->hiddenlabel()
                                            ->placeholder('นามสกุล')
                                            ->readOnly(),

                                        DatePicker::make('date_of_birth')
                                            ->hiddenlabel()
                                            ->placeholder('วัน/เดือน/ปี เกิด')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->locale('th')
                                            ->buddhist()
                                            ->readOnly(),

                                        TextInput::make('id_card')
                                            ->hiddenlabel()
                                            ->label('เลขบัตรประชาชน')
                                            ->columnSpan(1)
                                            ->mask('9-9999-99999-99-9')
                                            ->placeholder('รหัสบัตรประชาชน (กรอกเฉพาะตัวเลข)')
                                            ->readOnly(),

                                        TextInput::make('tel')
                                            ->columnSpan(1)
                                            ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                                            ->mask('999-999-9999')
                                            ->hiddenlabel()
                                            ->tel()
                                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                                            ->readOnly(),

                                        Select::make('marital_status')
                                            ->hiddenlabel()
                                            ->placeholder('สถานภาพสมรส')
                                            ->options(config('iconf.marital_status'))
                                            ->disabled(),
                                    ])->collapsed(),

                                Section::make('ที่อยู่')
                                    ->columns(3)
                                    ->relationship('userHasOneResumeToLocation')
                                    ->schema([
                                        Textarea::make('address')
                                            ->hiddenlabel()
                                            ->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                                            ->columnSpan(3)
                                            ->readOnly(),

                                        Select::make('province_id')
                                            ->options(Provinces::pluck('name_th', 'id'))
                                            ->live()
                                            ->preload()
                                            ->hiddenlabel()
                                            ->placeholder('จังหวัด')
                                            ->searchable()
                                            ->disabled(),

                                        Select::make('district_id')
                                            ->options(function (Get $get) {
                                                return Districts::query()
                                                    ->where('province_id', $get('province_id'))
                                                    ->pluck('name_th', 'id');
                                            })
                                            ->live()
                                            ->preload()
                                            ->hiddenlabel()
                                            ->placeholder('อำเภอ')
                                            ->searchable()
                                            ->disabled(),

                                        Select::make('subdistrict_id')
                                            ->options(function (Get $get) {
                                                return Subdistricts::query()
                                                    ->where('district_id', $get('district_id'))
                                                    ->pluck('name_th', 'id');
                                            })
                                            ->hiddenlabel()
                                            ->preload()
                                            ->placeholder('ตำบล')
                                            ->live()
                                            ->searchable()
                                            ->disabled(),

                                        TextInput::make('zipcode')
                                            ->live()
                                            ->hiddenlabel()
                                            ->placeholder('รหัสไปรษณีย์')
                                            ->readOnly(),
                                    ])->collapsed(),

                                Section::make('ประวัติการศึกษา')
                                    ->schema([
                                        Repeater::make('educations')
                                            ->hiddenLabel()
                                            ->relationship('userHasmanyResumeToEducation')
                                            ->schema([
                                                Fieldset::make('education')
                                                    ->hiddenLabel()
                                                    ->columns(3)
                                                    ->contained(false)
                                                    ->schema([
                                                        TextInput::make('institution')
                                                            ->hiddenlabel()
                                                            ->placeholder('ระบุสถาบันที่จบการศึกษา')
                                                            ->label('สถาบัน')
                                                            ->prefix('สถาบัน')
                                                            ->readOnly(),

                                                        TextInput::make('degree')
                                                            ->hiddenlabel()
                                                            ->label('ชื่อปริญญา')
                                                            ->prefix('ชื่อปริญญา')
                                                            ->placeholder('เช่น วิศวกรรมศาสตร์บัณฑิต')
                                                            ->readOnly(),

                                                        TextInput::make('education_level')
                                                            ->hiddenlabel()
                                                            ->label('ระดับการศึกษา')
                                                            ->prefix('ระดับการศึกษา')
                                                            ->placeholder('เช่น ปริญญาตรี')
                                                            ->readOnly(),

                                                        TextInput::make('faculty')
                                                            ->hiddenlabel()
                                                            ->label('คณะ')
                                                            ->prefix('คณะ')
                                                            ->placeholder('เช่น วิศวกรรมศาสตร์')
                                                            ->readOnly(),

                                                        TextInput::make('major')
                                                            ->hiddenlabel()
                                                            ->label('สาขาวิชา')
                                                            ->prefix('สาขาวิชา')
                                                            ->placeholder('เช่น โยธา')
                                                            ->readOnly(),

                                                        Select::make('last_year')
                                                            ->label('ปีจบการศึกษา')
                                                            ->prefix('ปีจบการศึกษา')
                                                            ->hiddenlabel()
                                                            ->placeholder('ปีจบการศึกษา')
                                                            ->nullable()
                                                            ->options(array_combine($years_education_AD, $years_education_BE))
                                                            ->disabled(),

                                                        TextInput::make('gpa')
                                                            ->hiddenLabel()
                                                            ->label('เกรดเฉลี่ย')
                                                            ->prefix('เกรดเฉลี่ย')
                                                            ->placeholder('เกรดเฉลี่ย')
                                                            ->numeric()
                                                            ->inputMode('decimal')
                                                            ->mask('9.99')
                                                            ->readOnly(),
                                                    ]),
                                            ]),
                                    ])->collapsed(),

                                Section::make('ประสบการณ์การทำงาน')
                                    ->schema([
                                        Repeater::make('experiences')
                                            ->hiddenLabel()
                                            ->relationship('userHasmanyResumeToWorkExperiences')
                                            ->schema([
                                                Fieldset::make('details')
                                                    ->hiddenLabel()
                                                    ->columns(2)
                                                    ->contained(false)
                                                    ->schema([
                                                        TextInput::make('company')
                                                            ->hiddenlabel()
                                                            ->placeholder('บริษัทที่เคยทำงาน')
                                                            ->label('บริษัท')
                                                            ->prefix('บริษัท')
                                                            ->readOnly(),

                                                        TextInput::make('position')
                                                            ->hiddenlabel()
                                                            ->label('ตำแหน่ง')
                                                            ->prefix('ตำแหน่ง')
                                                            ->placeholder('ตำแหน่งเดิมที่เคยทำงาน')
                                                            ->readOnly(),

                                                        TextInput::make('duration')
                                                            ->hiddenlabel()
                                                            ->label('ช่วงเวลา')
                                                            ->prefix('ช่วงเวลา')
                                                            ->placeholder('เช่น ม.ค 2540 - ม.ค 2550')
                                                            ->readOnly(),

                                                        TextInput::make('salary')
                                                            ->hiddenlabel()
                                                            ->label('เงินเดือน')
                                                            ->prefix('เงินเดือน')
                                                            ->placeholder('เงินเดือนที่เคยได้จากตำแหน่งนั้น')
                                                            ->readOnly(),

                                                        TextArea::make('details')
                                                            ->label('รายละเอียด')
                                                            ->placeholder('กรอกรายละเอียดเนื้องาน')
                                                            ->columnSpan(2)
                                                            ->readOnly(),
                                                    ]),
                                            ]),
                                    ])->collapsed(),

                                AdvancedFileUpload::make('resume')
                                    ->pdfPreviewHeight(400)
                                    ->pdfDisplayPage(1)
                                    ->pdfToolbar(true)
                                    ->pdfZoomLevel(100)
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->pdfNavPanes(true)
                                    ->label('เลือกไฟล์')
                                    ->openable()
                                    ->disabled()
                                    ->deletable(false)
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),

                        Tab::make('idcard')
                            ->label('บัตรประชาชน')
                            ->tabslug('idcard')
                            ->schema([
                                Section::make('ข้อมูลทั่วไป')
                                    ->columns(3)
                                    ->relationship('userHasoneIdcard')
                                    ->schema([
                                        TextInput::make('prefix_name_th')
                                            ->label('คำนำหน้าชื่อภาษาไทย')
                                            ->placeholder('คำนำหน้าชื่อ')
                                            ->readOnly(),

                                        TextInput::make('name_th')
                                            ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                            ->label('ชื่อภาษาไทย')
                                            ->readOnly(),

                                        TextInput::make('last_name_th')
                                            ->placeholder('กรอกหรือแก้ไขนามสกุลถ้าข้อมูลผิดพลาด')
                                            ->label('นามสกุลภาษาไทย')
                                            ->readOnly(),

                                        TextInput::make('prefix_name_en')
                                            ->label('คำนำหน้าชื่อภาษาอังกฤษ')
                                            ->placeholder('PreFix Name')
                                            ->readOnly(),

                                        TextInput::make('name_en')
                                            ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                            ->label('ชื่อภาษาอังกฤษ')
                                            ->readOnly(),

                                        TextInput::make('last_name_en')
                                            ->placeholder('กรอกหรือแก้ไขนามสกุลถ้าข้อมูลผิดพลาด')
                                            ->label('นามสกุลภาษาอังกฤษ')
                                            ->readOnly(),

                                        TextInput::make('id_card_number')
                                            ->label('เลขบัตรประชาชน')
                                            ->mask('9-9999-99999-99-9')
                                            ->placeholder('รหัสบัตรประชาชน (กรอกเฉพาะตัวเลข)')
                                            ->readOnly(),

                                        DatePicker::make('date_of_birth')
                                            ->label('วัน/เดือน/ปี เกิด')
                                            ->placeholder('วัน/เดือน/ปี เกิด')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->locale('th')
                                            ->buddhist()
                                            ->live()
                                            ->readOnly(),

                                        TextInput::make('age_id_card')
                                            ->placeholder(function (Get $get) {
                                                return empty($get('date_of_birth'))
                                                    ? 'ต้องกรอกวันเกิดเพื่อคำนวณอายุ'
                                                    : Carbon::parse($get('date_of_birth'))->age;
                                            })
                                            ->suffix('ปี')
                                            ->label('อายุ')
                                            ->readOnly()
                                            ->dehydrated(false),

                                        TextInput::make('religion')
                                            ->placeholder('กรอกหรือแก้ไขศาสนาที่คุณนับถือ')
                                            ->label('ศาสนา')
                                            ->readOnly(),

                                        DatePicker::make('date_of_issue')
                                            ->label('วันออกบัตร')
                                            ->placeholder('date_of_issue')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->locale('th')
                                            ->buddhist()
                                            ->readOnly(),

                                        DatePicker::make('date_of_expiry')
                                            ->label('วันบัตรหมดอายุ')
                                            ->placeholder('วันบัตรหมดอายุ')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->locale('th')
                                            ->buddhist()
                                            ->readOnly(),
                                    ]),

                                Section::make('ที่อยู่ตามบัตรประชาชน')
                                    ->columns(3)
                                    ->relationship('userHasoneIdcard')
                                    ->schema([
                                        Textarea::make('address')
                                            ->hiddenlabel()
                                            ->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                                            ->columnSpan(3)
                                            ->readOnly(),

                                        Select::make('province_id')
                                            ->options(Provinces::pluck('name_th', 'id'))
                                            ->live()
                                            ->preload()
                                            ->hiddenlabel()
                                            ->placeholder('จังหวัด')
                                            ->searchable()
                                            ->disabled()
                                            ->afterStateUpdated(function (Select $column, Set $set) {
                                                $state = $column->getState();
                                                if ($state == null) {
                                                    $set('province_id', null);
                                                    $set('district_id', null);
                                                    $set('subdistrict_id', null);
                                                    $set('zipcode', null);
                                                }
                                            }),

                                        Select::make('district_id')
                                            ->options(function (Get $get) {
                                                return Districts::where('province_id', $get('province_id'))
                                                    ->pluck('name_th', 'id');
                                            })
                                            ->live()
                                            ->preload()
                                            ->hiddenlabel()
                                            ->placeholder('อำเภอ')
                                            ->searchable()
                                            ->disabled()
                                            ->afterStateUpdated(function (Set $set) {
                                                $set('subdistrict_id', null);
                                                $set('zipcode', null);
                                            }),

                                        Select::make('subdistrict_id')
                                            ->options(function (Get $get) {
                                                return Subdistricts::where('district_id', $get('district_id'))
                                                    ->pluck('name_th', 'id');
                                            })
                                            ->hiddenlabel()
                                            ->preload()
                                            ->placeholder('ตำบล')
                                            ->live()
                                            ->searchable()
                                            ->disabled()
                                            ->afterStateUpdated(function (Select $column, Set $set) {
                                                $state = $column->getState();
                                                $zipcode = Subdistricts::where('id', $state)->pluck('zipcode');
                                                $set('zipcode', Str::slug($zipcode));
                                            }),

                                        TextInput::make('zipcode')
                                            ->live()
                                            ->hiddenlabel()
                                            ->placeholder('รหัสไปรษณีย์')
                                            ->readOnly(),
                                    ])->collapsed(),

                                AdvancedFileUpload::make('idcard')
                                    ->pdfPreviewHeight(400)
                                    ->pdfDisplayPage(1)
                                    ->pdfToolbar(true)
                                    ->pdfZoomLevel(100)
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->pdfNavPanes(true)
                                    ->label('เลือกไฟล์')
                                    ->openable()
                                    ->deletable(false)
                                    ->disabled()
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()
                                            ->where('file_name', $component->getName())
                                            ->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        return $doc ? 0 : 1;
                                    }),

                            ]),
                        Tab::make('วุฒิการศึกษา')
                            ->tabslug('transcript')
                            ->schema([
                                Section::make('ข้อมูลทั่วไป')
                                    ->columns(3)
                                    ->relationship('userHasoneTranscript')
                                    ->schema([
                                        TextInput::make('prefix_name')
                                            ->placeholder('ระบุคำนำหน้าชื่อ')
                                            ->label('ชื่อ')
                                            ->readOnly(),

                                        TextInput::make('name')
                                            ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                            ->label('ชื่อ')
                                            ->readOnly(),

                                        TextInput::make('last_name')
                                            ->placeholder('กรอกหรือแก้ไขนามสกุลถ้าข้อมูลผิดพลาด')
                                            ->label('นามสกุล')
                                            ->readOnly(),

                                        TextInput::make('institution')
                                            ->label('สถาบัน/มหาวิทยาลัย')
                                            ->placeholder('กรอกชื่อสถาบันการศึกษา')
                                            ->readOnly(),

                                        TextInput::make('degree')
                                            ->label('ชื่อวุฒิการศึกษา')
                                            ->placeholder('เช่น วิศวกรรมศาสตรบัณฑิต')
                                            ->readOnly(),

                                        TextInput::make('education_level')
                                            ->label('ระดับการศึกษา')
                                            ->placeholder('เช่น ปริญญาตรี')
                                            ->readOnly(),

                                        TextInput::make('faculty')
                                            ->label('คณะ')
                                            ->placeholder('กรอกชื่อคณะ')
                                            ->readOnly(),

                                        TextInput::make('major')
                                            ->label('สาขาวิชา')
                                            ->placeholder('กรอกชื่อสาขาวิชา')
                                            ->readOnly(),

                                        TextInput::make('minor')
                                            ->label('วิชาโท')
                                            ->placeholder('กรอกชื่อวิชาโท')
                                            ->readOnly(),

                                        DatePicker::make('date_of_admission')
                                            ->label('วันที่เข้ารับการศึกษา')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->locale('th')
                                            ->buddhist()
                                            ->readOnly(),
                                        DatePicker::make('date_of_graduation')
                                            ->label('วันสำเร็จการศึกษา')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->locale('th')
                                            ->buddhist()
                                            ->readOnly(),
                                        TextInput::make('gpa')
                                            ->label('เกรดเฉลี่ย (GPA)')
                                            ->placeholder('ตัวอย่าง 3.50')
                                            ->numeric()
                                            ->step(0.01)
                                            ->maxValue(4.00)
                                            ->readOnly(),
                                    ]),

                                AdvancedFileUpload::make('transcript')
                                    ->pdfPreviewHeight(400)
                                    ->pdfDisplayPage(1)
                                    ->pdfToolbar(true)
                                    ->pdfZoomLevel(100)
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->pdfNavPanes(true)
                                    ->label('เลือกไฟล์')
                                    ->openable()
                                    ->deletable(false)
                                    ->multiple()
                                    ->disabled()
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()
                                            ->where('file_name', $component->getName())
                                            ->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ]),

                        Tab::make('สมุดบัญชีธนาคาร')
                            ->tabslug('bookbank')
                            ->schema([
                                Section::make('ข้อมูลทั่วไป')
                                    ->columns(3)
                                    ->relationship('userHasoneBookbank')
                                    ->schema([
                                        TextInput::make('name')
                                            ->placeholder('กรอกหรือแก้ไขชื่อ')
                                            ->label('ชื่อ')
                                            ->readOnly(),

                                        TextInput::make('bank_name')
                                            ->placeholder('กรอกชื่อธนาคาร')
                                            ->label('ชื่อธนาคาร')
                                            ->readOnly(),

                                        TextInput::make('bank_id')
                                            ->label('เลขที่บัญชี')
                                            ->placeholder('กรอกเลขที่บัญชี')
                                            ->readOnly(),
                                    ]),

                                AdvancedFileUpload::make('bookbank')
                                    ->pdfPreviewHeight(400)
                                    ->pdfDisplayPage(1)
                                    ->pdfToolbar(true)
                                    ->pdfZoomLevel(100)
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->pdfNavPanes(true)
                                    ->label('เลือกไฟล์')
                                    ->openable()
                                    ->deletable(false)
                                    ->multiple()
                                    ->disabled()
                                    ->visibility('public')
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()
                                            ->where('file_name', $component->getName())
                                            ->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->hidden(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        return $doc ? 0 : 1;
                                    }),
                            ])

                    ])->columnSpanFull()->persistTabInQueryString()

            ]);
    }
}
