<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Districts;
use App\Models\Provinces;
use Illuminate\Support\Str;
use App\Models\Subdistricts;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Jobs\ProcessEmpDocJob;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
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
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('คลิกที่นี่เพื่อเปลี่ยน Email หรือ Password')
                    ->description('ท่านสามารถแก้ไขอีเมลหรือรหัสผ่านได้ หรือ จะไม่แก้ไขก็ได้')
                    ->hiddenLabel()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])->columns(3)->collapsed(),
                $this->getDataResume(),
            ]);
    }

    public function getDataResume() //ข้อมูลพนักงาน
    {
        $currentYear_BE = date('Y') + 543; // เช่น พ.ศ. 2025 + 543 = 2568
        $years_education_BE = range($currentYear_BE - 30, $currentYear_BE); // 40 ปีย้อนหลัง

        $currentYear_AD = date('Y'); // เช่น ค.ศ. 2025
        $years_education_AD = range($currentYear_AD - 30, $currentYear_AD); // 40 ปีย้อนหลัง
        //dump(array_combine($years_education_AD, $years_education_BE));
        return
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
                            //->avatar()
                            ->directory('emp_files')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/$component->getName().{$extension}";
                            })
                            ->afterStateHydrated(function ($component, $state) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->hidden(function ($component, $record) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                return $doc ? 0 : 1;
                            })
                            //->maxSize(3000)
                            //->columnSpan(2)
                            ->image(),
                        // ->imageEditor()
                        // ->imageEditorAspectRatios([
                        //     '16:9',
                        //     '4:3',
                        //     '1:1',
                        // ]),
                        Section::make('ข้อมูลทั่วไป')
                            //->label('ข้อมูลทั่วไป')
                            ->columns(3)
                            ->relationship('userHasoneResume')
                            ->schema([
                                Select::make('prefix_name')
                                    ->hiddenlabel()
                                    ->placeholder('คำนำหน้าชื่อ')
                                    ->options(config("iconf.prefix_name")),
                                TextInput::make('name')
                                    ->hiddenlabel()
                                    ->placeholder('ชื่อ'),
                                TextInput::make('last_name')
                                    ->hiddenlabel()
                                    ->placeholder('นามสกุล'),
                                DatePicker::make('date_of_birth')
                                    ->hiddenlabel()
                                    ->placeholder('วัน/เดือน/ปี เกิด')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),
                                TextInput::make('id_card')->hiddenlabel()
                                    ->label('เลขบัตรประชาชน')
                                    ->columnSpan(1)
                                    //->required()
                                    ->mask('9-9999-99999-99-9')
                                    ->placeholder('รหัสบัตรประชาชน (กรอกเฉพาะตัวเลข)'),
                                TextInput::make('tel')
                                    ->columnSpan(1)
                                    ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                                    ->mask('999-999-9999')
                                    ->hiddenlabel()
                                    ->tel()
                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                                Select::make('marital_status')
                                    ->hiddenlabel()
                                    ->placeholder('สถานภาพสมรส')
                                    ->options(config('iconf.marital_status'))
                            ])->collapsed(),
                        Section::make('ที่อยู่')
                            //->hiddenLabel()
                            ->columns(3)
                            //->contained(false)
                            ->relationship('userHasoneResumeToLocation')
                            ->schema([
                                Textarea::make('address')
                                    ->hiddenlabel()->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                                    ->columnSpan(3),
                                Select::make('province_id')
                                    ->options(Provinces::pluck('name_th', 'id'))
                                    ->live()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->preload()
                                    ->hiddenlabel()
                                    ->placeholder('จังหวัด')
                                    ->searchable()
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
                                        $data = Districts::query()
                                            ->where('province_id', $get('province_id'))
                                            ->pluck('name_th', 'id');
                                        return $data;
                                    })
                                    ->live()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->preload()
                                    ->hiddenlabel()
                                    ->placeholder('อำเภอ')
                                    ->searchable()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('subdistrict_id', null);
                                        $set('zipcode', null);
                                    }),
                                Select::make('subdistrict_id')
                                    ->options(function (Get $get) {
                                        $data = Subdistricts::query()
                                            ->where('district_id', $get('district_id'))
                                            ->pluck('name_th', 'id');
                                        return $data;
                                    })
                                    ->hiddenlabel()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->preload()
                                    ->placeholder('ตำบล')
                                    ->live()
                                    ->searchable()
                                    ->afterStateUpdated(function (Select $column, Set $set) {
                                        $state = $column->getState(); //รับค่าปัจจุบันในฟิลด์นี้หลังที่ Input ข้อมูลแล้ว
                                        $zipcode = Subdistricts::where('id', $state)->pluck('zipcode'); //ไปที่ Subdistrict โดยที่ id = ปัจจุบันที่เราเลือก
                                        $set('zipcode', Str::slug($zipcode)); //เอาค่าที่ได้ซึ่งเป็นอาเรย์มาถอดให้เหลือค่าอย่างเดียวด้วย Str::slug()แล้วเอาค่าที่ได้มาใส่ และส่งค่าไปยัง ฟิลด์ที่เลือกในที่นี้คือ zipcode
                                    }),
                                TextInput::make('zipcode')
                                    ->live()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->hiddenlabel()
                                    ->placeholder('รหัสไปรษณีย์')
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
                                                    ->prefix('สถาบัน'),
                                                TextInput::make('degree')
                                                    ->hiddenlabel()
                                                    ->label('ชื่อปริญญา')
                                                    ->prefix('ชื่อปริญญา')
                                                    ->placeholder('เช่น วิศวกรรมศาสตร์บัณฑิต'),
                                                TextInput::make('education_level')
                                                    ->hiddenlabel()
                                                    ->label('ระดับการศึกษา')
                                                    ->prefix('ระดับการศึกษา')
                                                    ->placeholder('เช่น ปริญญาตรี'),
                                                TextInput::make('faculty')
                                                    ->hiddenlabel()
                                                    ->label('คณะ')
                                                    ->prefix('คณะ')
                                                    ->placeholder('เช่น วิศวกรรมศาสตร์'),
                                                TextInput::make('major')
                                                    ->hiddenlabel()
                                                    ->label('สาขาวิชา')
                                                    ->prefix('สาขาวิชา')
                                                    ->placeholder('เช่น โยธา'),
                                                Select::make('last_year')
                                                    ->label('ปีจบการศึกษา')
                                                    ->prefix('ปีจบการศึกษา')
                                                    ->hiddenlabel()
                                                    ->placeholder('ปีจบการศึกษา')
                                                    ->nullable()
                                                    ->options(array_combine($years_education_AD, $years_education_BE)) // key = value เป็น พ.ศ.
                                                    ->placeholder('เลือกปี พ.ศ.'),
                                                TextInput::make('gpa')
                                                    ->hiddenLabel()
                                                    ->label('เกรดเฉลี่ย')
                                                    ->prefix('เกรดเฉลี่ย')
                                                    ->placeholder('เกรดเฉลี่ย')
                                                    ->numeric()
                                                    ->inputMode('decimal')
                                                    ->mask('9.99')

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
                                                    ->prefix('บริษัท'),
                                                TextInput::make('position')
                                                    ->hiddenlabel()
                                                    ->label('ตำแหน่ง')
                                                    ->prefix('ตำแหน่ง')
                                                    ->placeholder('ตำแหน่งเดิมที่เคยทำงาน'),
                                                TextInput::make('duration')
                                                    ->hiddenlabel()
                                                    ->label('ช่วงเวลา')
                                                    ->prefix('ช่วงเวลา')
                                                    ->placeholder('เช่น ม.ค 2540 - ม.ค 2550'),
                                                TextInput::make('salary')
                                                    ->hiddenlabel()
                                                    ->label('เงินเดือน')
                                                    ->prefix('เงินเดือน')
                                                    ->placeholder('เงินเดือนที่เคยได้จากตำแหน่งนั้น'),
                                                TextArea::make('details')
                                                    ->label('รายละเอียด')
                                                    ->placeholder('กรอกรายละเอียดเนื้องาน')
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ])->collapsed(),
                        AdvancedFileUpload::make('resume')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->disabled()
                            ->deletable(false)
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/$component->getName().{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->hidden(function ($component, $record) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                return $doc ? 0 : 1;
                            })

                    ]),
                Tab::make('idcard')
                    ->label('บัตรประชาชน')
                    ->tabslug('idcard')
                    ->schema([
                        Section::make('ข้อมูลทั่วไป')
                            //->label('ข้อมูลทั่วไป')
                            ->columns(3)
                            ->relationship('userHasoneIdcard')
                            ->schema([
                                TextInput::make('prefix_name_th')
                                    ->label('คำนำหน้าชื่อภาษาไทย')
                                    ->placeholder('คำนำหน้าชื่อ'),
                                TextInput::make('name_th')
                                    ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                    ->label('ชื่อภาษาไทย'),
                                TextInput::make('last_name_th')
                                    ->placeholder('กรอกหรือแก้ไขนามสกุลถ้าข้อมูลผิดพลาด')
                                    ->label('นามสกุลภาษาไทย'),
                                TextInput::make('prefix_name_en')
                                    ->label('คำนำหน้าชื่อภาษาอังกฤษ')
                                    ->placeholder('PreFix Name'),
                                TextInput::make('name_en')
                                    ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                    ->label('ชื่อภาษาอังกฤษ'),
                                TextInput::make('last_name_en')
                                    ->placeholder('กรอกหรือแก้ไขนามสกุลถ้าข้อมูลผิดพลาด')
                                    ->label('นามสกุลภาษาอังกฤษ'),
                                TextInput::make('id_card_number')
                                    ->label('เลขบัตรประชาชน')
                                    ->mask('9-9999-99999-99-9')
                                    ->placeholder('รหัสบัตรประชาชน (กรอกเฉพาะตัวเลข)'),
                                DatePicker::make('date_of_birth')
                                    ->label('วัน/เดือน/ปี เกิด')
                                    ->placeholder('วัน/เดือน/ปี เกิด')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist()
                                    ->live(),
                                // ->afterStateUpdated(function ($state, Set $set) {
                                //     if ($state) {
                                //         $age = Carbon::parse($state)->age;
                                //         dump($state);
                                //         dump('-----------');
                                //         dump($age);
                                //         $set('age_id_card',  $age);
                                //     } else {
                                //         $set('age_id_card', null);
                                //     }
                                // }),
                                TextInput::make('age_id_card')
                                    ->placeholder(function (Get $get) {
                                        return empty($get('date_of_birth'))
                                            ? 'ต้องกรอกวันเกิดเพื่อคำนวณอายุ'
                                            : Carbon::parse($get('date_of_birth'))->age;
                                    })
                                    //->live()
                                    //->autofocus()
                                    ->suffix('ปี')
                                    ->label('อายุ')
                                    ->readOnly() // ทำให้เป็นแบบอ่านอย่างเดียว
                                    ->dehydrated(false), // ป้องกันไม่ให้บันทึกค่านี้ลง DB/ สำคัญ: ป้องกันไม่ให้ Filament พยายามบันทึกค่านี้
                                TextInput::make('religion')
                                    ->placeholder('กรอกหรือแก้ไขศาสนาที่คุณนับถือ')
                                    ->label('ศาสนา'),
                                DatePicker::make('date_of_issue')
                                    ->label('วันออกบัตร')
                                    ->placeholder('date_of_issue')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),
                                DatePicker::make('date_of_expiry')
                                    ->label('วันบัตรหมดอายุ')
                                    ->placeholder('วันบัตรหมดอายุ')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),

                            ]), //->collapsed(),
                        Section::make('ที่อยู่ตามบัตรประชาชน')
                            //->label('ที่อยู่ตามบัตรประชาชน')
                            //->hiddenLabel()
                            ->columns(3)
                            //->contained(false)
                            ->relationship('userHasoneIdcard')
                            ->schema([
                                Textarea::make('address')
                                    ->hiddenlabel()->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                                    ->columnSpan(3),
                                Select::make('province_id')
                                    ->options(Provinces::pluck('name_th', 'id'))
                                    ->live()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->preload()
                                    ->hiddenlabel()
                                    ->placeholder('จังหวัด')
                                    ->searchable()
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
                                        $data = Districts::query()
                                            ->where('province_id', $get('province_id'))
                                            ->pluck('name_th', 'id');
                                        return $data;
                                    })
                                    ->live()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->preload()
                                    ->hiddenlabel()
                                    ->placeholder('อำเภอ')
                                    ->searchable()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('subdistrict_id', null);
                                        $set('zipcode', null);
                                    }),
                                Select::make('subdistrict_id')
                                    ->options(function (Get $get) {
                                        $data = Subdistricts::query()
                                            ->where('district_id', $get('district_id'))
                                            ->pluck('name_th', 'id');
                                        return $data;
                                    })
                                    ->hiddenlabel()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->preload()
                                    ->placeholder('ตำบล')
                                    ->live()
                                    ->searchable()
                                    ->afterStateUpdated(function (Select $column, Set $set) {
                                        $state = $column->getState(); //รับค่าปัจจุบันในฟิลด์นี้หลังที่ Input ข้อมูลแล้ว
                                        $zipcode = Subdistricts::where('id', $state)->pluck('zipcode'); //ไปที่ Subdistrict โดยที่ id = ปัจจุบันที่เราเลือก
                                        $set('zipcode', Str::slug($zipcode)); //เอาค่าที่ได้ซึ่งเป็นอาเรย์มาถอดให้เหลือค่าอย่างเดียวด้วย Str::slug()แล้วเอาค่าที่ได้มาใส่ และส่งค่าไปยัง ฟิลด์ที่เลือกในที่นี้คือ zipcode
                                    }),
                                TextInput::make('zipcode')
                                    ->live()
                                    // ->columnSpan([
                                    //     'default' => 2,
                                    //     'md' => 1
                                    // ])
                                    ->hiddenlabel()
                                    ->placeholder('รหัสไปรษณีย์')
                            ])->collapsed(),
                        AdvancedFileUpload::make('idcard')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->disabled()
                            ->deletable(false)
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/$component->getName().{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->hidden(function ($component, $record) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                return $doc ? 0 : 1;
                            })

                    ]),
                Tab::make('วุฒิการศึกษา')
                    ->tabslug('transcript')
                    ->schema([
                        Section::make('ข้อมูลทั่วไป')
                            //->label('ข้อมูลทั่วไป')
                            ->columns(3)
                            ->relationship('userHasoneTranscript')
                            ->schema([
                                TextInput::make('prefix_name')
                                    ->placeholder('ระบุคำนำหน้าชื่อ')
                                    ->label('ชื่อ'),
                                TextInput::make('name')
                                    ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                    ->label('ชื่อ'),
                                TextInput::make('last_name')
                                    ->placeholder('กรอกหรือแก้ไขนามสกุลถ้าข้อมูลผิดพลาด')
                                    ->label('นามสกุล'),
                                TextInput::make('institution')
                                    ->label('สถาบัน/มหาวิทยาลัย')
                                    ->placeholder('กรอกชื่อสถาบันการศึกษา'),
                                TextInput::make('degree')
                                    ->label('ชื่อวุฒิการศึกษา')
                                    ->placeholder('เช่น วิศวกรรมศาสตรบัณฑิต หรือ ศิลปศาสตรมหาบัณฑิต'),
                                TextInput::make('education_level') // อาจพิจารณาใช้ Select::make() เพื่อให้เลือกจากตัวเลือกที่กำหนด (เช่น ปริญญาตรี, ปริญญาโท)
                                    ->label('ระดับการศึกษา')
                                    ->placeholder('เช่น ปริญญาตรี, ปริญญาโท, มัธยมศึกษาปีที่ 6'),
                                TextInput::make('faculty')
                                    ->label('คณะ')
                                    ->placeholder('กรอกชื่อคณะ'),
                                TextInput::make('major')
                                    ->label('สาขาวิชา')
                                    ->placeholder('กรอกชื่อสาขาวิชา'),
                                TextInput::make('minor')
                                    ->label('วิชาโท')
                                    ->placeholder('กรอกชื่อวิชาโท (หากไม่มีให้ว่างไว้)'),
                                DatePicker::make('date_of_admission')
                                    ->label('วันที่เข้ารับการศึกษา')
                                    ->placeholder('วันที่เข้ารับการศึกษา')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),
                                DatePicker::make('date_of_graduation')
                                    ->label('วันสำเร็จการศึกษา')
                                    ->placeholder('วันสำเร็จการศึกษา')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),
                                TextInput::make('gpa') // แนะนำให้ใช้ DecimalInput เพื่อควบคุมรูปแบบทศนิยม
                                    ->label('เกรดเฉลี่ย (GPA)')
                                    ->placeholder('กรอกเกรดเฉลี่ย (เช่น 3.50)')
                                    ->numeric()
                                    ->step(0.01) // ให้รับค่าทศนิยมสองตำแหน่ง
                                    ->maxValue(4.00), // กำหนดค่าสูงสุด
                            ]),
                        AdvancedFileUpload::make('transcript')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->deletable(false)
                            ->multiple()
                            ->disabled()
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component, $state) {
                                $i = mt_rand(1000, 9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/$component->getName()_{$i}.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component, $state) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->hidden(function ($component, $record) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                return $doc ? 0 : 1;
                            })
                    ]),
                Tab::make('สมุดบัญชีธนาคาร')
                    ->tabslug('bookbank')
                    ->schema([
                        Section::make('ข้อมูลทั่วไป')
                            //->label('ข้อมูลทั่วไป')
                            ->columns(3)
                            ->relationship('userHasoneBookbank')
                            ->schema([
                                TextInput::make('name')
                                    ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                    ->label('ชื่อ'),
                                TextInput::make('bank_name')
                                    ->placeholder('กรอกหรือแก้ไข้ชื่อบัญชีธนาคาร')
                                    ->label('ชื่อธนาคาร'),
                                TextInput::make('bank_id')
                                    ->label('เลขที่บัญชี')
                                    ->placeholder('กรอกหรือแก้ไขเลขที่บัญชีธนาคาร'),
                            ]),
                        AdvancedFileUpload::make('bookbank')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->deletable(false)
                            ->multiple()
                            ->disabled()
                            //->panelLayout('grid')
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component, $state) {

                                $i = mt_rand(1000, 9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/$component->getName().{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component, $state) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->hidden(function ($component, $record) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                return $doc ? 0 : 1;
                            })
                    ]),
            ])->columnSpanFull()->persistTabInQueryString();
    }

    public function getLayout(): string
    {
        return 'filament-panels::components.layout.index';
    }

    protected function getRedirectUrl(): ?string
    {
        return env('APP_URL');
    }
    
}
