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
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Field;
use App\Jobs\ProcessAnotherEmpDocJob;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
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
    public bool $isMobile;

    public bool $isSubmitDisabledFromFile = true;
    public bool $isSubmitDisabledFromConfirm = true;

    public ?string $confirm = '
        <div>
            ฉันยอมยินยอมให้บริษัทนำข้อมูลของเก็บไว้เพื่อพิจารณาการรับสมัครงาน<br>
            <a 
            style="text-decoration: underline;"
            href="http://127.0.0.1" 
            target="_blank"
            >
                ดูรายละเอียดเงื่อนไข
            </a>
        </div>
    ';


    public function updateStateInFile($value)
    {
        $this->isSubmitDisabledFromFile = $value; // Disable if empty
    }

    public function updateStateInConfirm($value)
    {   //dump(!$value);
        $this->isSubmitDisabledFromConfirm = !$value;
    }

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
        $detect = new MobileDetect();
        $this->isMobile = $detect->isMobile();
        $currentYear_BE = date('Y') + 543; // เช่น พ.ศ. 2025 + 543 = 2568
        $years_education_BE = range($currentYear_BE - 30, $currentYear_BE); // 40 ปีย้อนหลัง

        $currentYear_AD = date('Y'); // เช่น ค.ศ. 2025
        $years_education_AD = range($currentYear_AD - 30, $currentYear_AD); // 40 ปีย้อนหลัง



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

                        Section::make('ข้อมูลทั่วไปจาก Resume')
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
                                    ->addable(false)
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
                                    ->addActionLabel('เพิ่ม "ประสบการณ์ทำงาน"')
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
                        Section::make('เรซูเม่')
                            ->id('resume')
                            ->collapsible()

                            ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                            ->footer(
                                function ($component) {
                                    return [
                                        Action::make('file_resume')
                                            ->button()
                                            ->label('อับเดตเอกสาร')
                                            ->action(function ($livewire, $component, $record) {

                                                $user_id = auth()->user()->id;
                                                $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม

                                                if (!empty($data[$component->getId()])) {
                                                    $record->userHasmanyDocEmp()->updateOrCreate(
                                                        ['file_name' => $component->getId()],
                                                        [
                                                            'user_id' => $record->id,
                                                            'file_name_th' => $component->getHeading(),
                                                            'path' => $data[$component->getId()],
                                                            'confirm' => $data['confirm'],
                                                        ]
                                                    );
                                                }
                                                ProcessEmpDocJob::dispatch(
                                                    $data[$component->getId()],
                                                    User::find($user_id),
                                                    $component->getId(),
                                                    $component->getHeading()
                                                );
                                            }),
                                        DeleteAction::make($component->getHeading())
                                            ->label("เคลียร์ข้อมูล")
                                            ->requiresConfirmation()
                                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"{$component->getHeading()}\" ทั้งหมด")
                                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"{$component->getHeading()}\ รวมถึงไฟล์ด้วยใช่หรือไม่")
                                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                                            ->action(function ($record, $component) {
                                                //dump($component->getHeading());
                                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                                                if (!empty($doc)) {

                                                    // 3.1 HasOne Relations (Location, JobPreference)
                                                    $record->userHasOneResumeToLocation()->delete();
                                                    $record->userHasOneResumeToJobPreference()->delete();

                                                    // 3.2 HasMany Relations (Education, Work Experiences, etc.)
                                                    $record->userHasManyResumeToEducation()->delete();
                                                    $record->userHasManyResumeToWorkExperiences()->delete();
                                                    $record->userHasManyResumeToLangSkill()->delete();
                                                    $record->userHasManyResumeToSkill()->delete();
                                                    $record->userHasManyResumeToCertificate()->delete();
                                                    $record->userHasManyResumeToOtherContact()->delete();

                                                    $record->userHasOneResume()->update([
                                                        'prefix_name' => null,      // คำนำหน้าชื่อ
                                                        'name' => null,             // ชื่อ
                                                        'last_name' => null,        // นามสกุล
                                                        'tel' => null,              // เบอร์โทรศัพท์
                                                        'date_of_birth' => null,    // วัน/เดือน/ปี เกิด
                                                        'marital_status' => null,   // สถานภาพสมรส
                                                        'id_card' => null,          // เลขบัตรประชาชน
                                                        'gender' => null,           // เพศ
                                                        'height' => null,           // ส่วนสูง
                                                        'weight' => null,           // น้ำหนัก
                                                        'military' => null,         // เกณฑ์ทหาร
                                                        'nationality' => null,      // สัญชาติ
                                                        'religion' => null,         // ศาสนา
                                                    ]);

                                                    Storage::disk('public')->delete($doc->path);
                                                    $doc->delete();
                                                    return redirect("/profile?tab={$component->getId()}::data::tab");
                                                }
                                            })
                                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                                    ];
                                }
                            )
                            ->schema([
                                AdvancedFileUpload::make('resume')
                                    ->hiddenLabel()
                                    ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->reorderable()
                                    ->openable()
                                    ->appendFiles()
                                    ->removeUploadedFileButtonPosition('right')
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->previewable(function () {
                                        return $this->isMobile ? 0 : 1;
                                    })
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                                        $i = mt_rand(1000, 9000);
                                        $extension = $file->getClientOriginalExtension();
                                        $userEmail = auth()->user()->email;
                                        return "{$userEmail}/{$component->getName()}/{$component->getName()}_{$i}.{$extension}";
                                    })
                                    ->deleteUploadedFileUsing(function ($state, $record, $component) {

                                        $doc = $record->userHasmanyDocEmp()
                                            ->where('file_name', $component->getName())
                                            ->first();
                                        $path = $doc->path;

                                        Storage::disk('public')->delete($path);
                                        $doc->delete();

                                        if ($doc) {
                                            // 3.1 HasOne Relations (Location, JobPreference)
                                            $record->userHasOneResumeToLocation()->delete(); // ต้องเรียกเมธอดที่สร้าง Relation
                                            $record->userHasOneResumeToJobPreference()->delete();

                                            // 3.2 HasMany Relations (Education, Work Experiences, etc.)
                                            $record->userHasManyResumeToEducation()->delete();
                                            $record->userHasManyResumeToWorkExperiences()->delete();
                                            $record->userHasManyResumeToLangSkill()->delete();
                                            $record->userHasManyResumeToSkill()->delete();
                                            $record->userHasManyResumeToCertificate()->delete();
                                            $record->userHasManyResumeToOtherContact()->delete();

                                            $record->userHasOneResume()->update([
                                                'prefix_name' => null,      // คำนำหน้าชื่อ
                                                'name' => null,             // ชื่อ
                                                'last_name' => null,        // นามสกุล
                                                'tel' => null,              // เบอร์โทรศัพท์
                                                'date_of_birth' => null,    // วัน/เดือน/ปี เกิด
                                                'marital_status' => null,   // สถานภาพสมรส
                                                'id_card' => null,          // เลขบัตรประชาชน
                                                'gender' => null,           // เพศ
                                                'height' => null,           // ส่วนสูง
                                                'weight' => null,           // น้ำหนัก
                                                'military' => null,         // เกณฑ์ทหาร
                                                'nationality' => null,      // สัญชาติ
                                                'religion' => null,         // ศาสนา
                                            ]);
                                        }
                                        return redirect("/profile?tab={$component->getName()}::data::tab");
                                    }),
                                Toggle::make('confirm')
                                    ->label(new HtmlString($this->confirm))
                                    ->accepted()
                                    ->live()
                                    ->afterStateHydrated(function () {
                                        $this->updateStateInConfirm(false);
                                    })
                                    ->default(false)
                                    ->validationMessages([
                                        'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                                    ])

                                    ->afterStateUpdated(function ($state) {
                                        $this->updateStateInConfirm($state);
                                    }),
                            ]),

                    ]),
                Tab::make('idcard')
                    ->label('บัตรประชาชน')
                    ->tabslug('idcard')
                    ->schema([
                        Section::make('ข้อมูลทั่วไป')
                            //->label('ข้อมูลทั่วไป')
                            ->columns(3)
                            ->relationship('userHasoneIdcard')
                            ->collapsed()
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
                        Section::make('บัตรประชาชน') //fileupload
                            ->id('idcard')
                            ->collapsible()
                            ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                            ->footer(
                                function ($component) {
                                    return [
                                        Action::make('file_idcard')
                                            ->label('อับเดตเอกสาร')
                                            ->action(function ($livewire, $component, $record) {
                                                $user_id = auth()->user()->id;
                                                $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม

                                                if (!empty($data[$component->getId()])) {
                                                    $record->userHasmanyDocEmp()->updateOrCreate(
                                                        ['file_name' => $component->getId()],
                                                        [
                                                            'user_id' => $record->id,
                                                            'file_name_th' => $component->getHeading(),
                                                            'path' => $data[$component->getId()],
                                                            'confirm' => $data['confirm'],
                                                        ]
                                                    );
                                                }
                                                ProcessEmpDocJob::dispatch(
                                                    $data[$component->getId()],
                                                    User::find($user_id),
                                                    $component->getId(),
                                                    $component->getHeading()
                                                );
                                            }),
                                        DeleteAction::make($component->getHeading())
                                            ->label("เคลียร์ข้อมูล")
                                            ->requiresConfirmation()
                                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"{$component->getHeading()}\" ทั้งหมด")
                                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"{$component->getHeading()}\ รวมถึงไฟล์ด้วยใช่หรือไม่")
                                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                                            ->action(function ($record, $component) {
                                                //dump($component->getHeading());
                                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                                                if (!empty($doc)) {
                                                    $record->userHasOneIdcard()->delete();
                                                    Storage::disk('public')->delete($doc->path);
                                                    $doc->delete();
                                                    return redirect("/profile?tab={$component->getId()}::data::tab");
                                                }
                                            })
                                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                                    ];
                                }
                            )
                            ->schema([
                                AdvancedFileUpload::make('idcard')
                                    ->hiddenLabel()
                                    ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->reorderable()
                                    ->openable()
                                    ->appendFiles()
                                    ->removeUploadedFileButtonPosition('right')
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->previewable(function () {
                                        return $this->isMobile ? 0 : 1;
                                    })
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                                        $i = mt_rand(1000, 9000);
                                        $extension = $file->getClientOriginalExtension();
                                        $userEmail = auth()->user()->email;
                                        return "{$userEmail}/{$component->getName()}/{$component->getName()}_{$i}.{$extension}";
                                    })
                                    ->deleteUploadedFileUsing(function ($state, $record, $component) {

                                        $doc = $record->userHasmanyDocEmp()
                                            ->where('file_name', $component->getName())
                                            ->first();
                                        $path = $doc->path;
                                        if (!empty($doc)) {
                                            $record->userHasOneIdcard()->delete();
                                            Storage::disk('public')->delete($path);
                                            $doc->delete();
                                            return redirect("/profile?tab={$component->getId()}::data::tab");
                                        }
                                        return redirect("/profile?tab={$component->getName()}::data::tab");
                                    }),
                                Toggle::make('confirm')
                                    ->label(new HtmlString($this->confirm))
                                    ->accepted()
                                    ->live()
                                    ->afterStateHydrated(function () {
                                        $this->updateStateInConfirm(false);
                                    })
                                    ->default(false)
                                    ->validationMessages([
                                        'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                                    ])

                                    ->afterStateUpdated(function ($state) {
                                        $this->updateStateInConfirm($state);
                                    }),
                            ]),
                    ]),
                Tab::make('วุฒิการศึกษา')
                    ->tabslug('transcript')
                    ->schema([
                        Section::make('ข้อมูลทั่วไป')
                            ->collapsed()
                            ->columns(3)
                            ->relationship('userHasoneTranscript')
                            ->schema([
                                TextInput::make('prefix_name')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->placeholder('ระบุคำนำหน้าชื่อ')
                                    ->label('ชื่อ'),
                                TextInput::make('name')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                                    ->label('ชื่อ'),
                                TextInput::make('last_name')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->placeholder('กรอกหรือแก้ไขนามสกุลถ้าข้อมูลผิดพลาด')
                                    ->label('นามสกุล'),
                                TextInput::make('institution')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->label('สถาบัน/มหาวิทยาลัย')
                                    ->placeholder('กรอกชื่อสถาบันการศึกษา'),
                                TextInput::make('degree')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->label('ชื่อวุฒิการศึกษา')
                                    ->placeholder('เช่น วิศวกรรมศาสตรบัณฑิต หรือ ศิลปศาสตรมหาบัณฑิต'),
                                TextInput::make('education_level') // อาจพิจารณาใช้ Select::make() เพื่อให้เลือกจากตัวเลือกที่กำหนด (เช่น ปริญญาตรี, ปริญญาโท)
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->label('ระดับการศึกษา')
                                    ->placeholder('เช่น ปริญญาตรี, ปริญญาโท, มัธยมศึกษาปีที่ 6'),
                                TextInput::make('faculty')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->label('คณะ')
                                    ->placeholder('กรอกชื่อคณะ'),
                                TextInput::make('major')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
                                    ->label('สาขาวิชา')
                                    ->placeholder('กรอกชื่อสาขาวิชา'),
                                TextInput::make('minor')
                                    ->formatStateUsing(fn($state) => ucwords($state ?? ''))
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
                        Section::make('วุฒิการศึกษา')
                            ->id('transcript')
                            ->collapsible()

                            ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                            ->footer(
                                function ($component) {
                                    return [
                                        Action::make('file_transcript')
                                            ->label('อับเดตเอกสาร')
                                            ->action(function ($livewire, $component, $record) {

                                                $user_id = auth()->user()->id;
                                                $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม

                                                if (!empty($data[$component->getId()])) {
                                                    $record->userHasmanyDocEmp()->updateOrCreate(
                                                        ['file_name' => $component->getId()],
                                                        [
                                                            'user_id' => $record->id,
                                                            'file_name_th' => $component->getHeading(),
                                                            'path' => $data[$component->getId()],
                                                            'confirm' => $data['confirm'],
                                                        ]
                                                    );
                                                }

                                                ProcessEmpDocJob::dispatch(
                                                    $data[$component->getId()],
                                                    User::find($user_id),
                                                    $component->getId(),
                                                    $component->getHeading()
                                                );
                                            }),
                                        DeleteAction::make($component->getHeading())
                                            ->label("เคลียร์ข้อมูล")
                                            ->requiresConfirmation()
                                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"{$component->getHeading()}\" ทั้งหมด")
                                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"{$component->getHeading()}\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                                            ->action(function ($record, $component) {
                                                //dump($component->getHeading());
                                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                                                if (!empty($doc)) {
                                                    $record->userHasoneTranscript()->delete();
                                                    Storage::disk('public')->delete($doc->path);
                                                    $doc->delete();
                                                    return redirect("/profile?tab={$component->getId()}::data::tab");
                                                }
                                            })
                                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                                    ];
                                }
                            )
                            ->schema([
                                AdvancedFileUpload::make('transcript')
                                    ->hiddenLabel()
                                    ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->multiple()
                                    ->reorderable()
                                    ->openable()
                                    ->appendFiles()
                                    ->removeUploadedFileButtonPosition('right')
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->previewable(function () {
                                        return $this->isMobile ? 0 : 1;
                                    })
                                    ->panelLayout(function () {
                                        return $this->isMobile ? null : 'grid';
                                    })
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                                        $i = mt_rand(1000, 9000);
                                        $extension = $file->getClientOriginalExtension();
                                        $userEmail = auth()->user()->email;
                                        return "{$userEmail}/{$component->getName()}/{$component->getName()}_{$i}.{$extension}";
                                    })
                                    ->deleteUploadedFileUsing(function ($state, $record, $component) {

                                        $doc = $record->userHasmanyDocEmp()
                                            ->where('file_name', $component->getName())
                                            ->first();
                                        $path = $doc->path;

                                        $fileDelete = array_values(array_diff($path, $state));
                                        if (count($path) > 1) {
                                            Storage::disk('public')->delete($fileDelete[0]);
                                            $pathSuccess = array_values(array_diff($path, $fileDelete));
                                            $record->userHasmanyDocEmp()->updateOrCreate(
                                                ['file_name' => $component->getName()],
                                                ['path' => $pathSuccess]
                                            );
                                        } else {
                                            Storage::disk('public')->delete($path);
                                            $doc->delete();
                                        }
                                        $doc_transcript = $record->userHasoneTranscript()
                                            ->where('user_id', $record->id)
                                            ->first();
                                        if (!empty($doc_transcript)) {
                                            $doc_transcript->delete();
                                        }
                                        return redirect("/profile?tab={$component->getName()}::data::tab");
                                    }),
                                Toggle::make('confirm')
                                    ->label(new HtmlString($this->confirm))
                                    ->accepted()
                                    ->live()
                                    ->afterStateHydrated(function () {
                                        $this->updateStateInConfirm(false);
                                    })
                                    ->default(false)
                                    ->validationMessages([
                                        'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                                    ])

                                    ->afterStateUpdated(function ($state) {
                                        $this->updateStateInConfirm($state);
                                    }),
                            ]),
                    ]),
                
                Tab::make('เอกสารเพิ่มเติม')
                    ->tabslug('another')
                    ->schema([
                        Repeater::make('anothers')
                            ->addable(false)
                            ->columns(3)
                            ->label('ข้อมูลเอกสารเพิ่มเติม')
                            ->itemLabel(fn(array $state): ?string => $state['doc_type'] ?? null)
                            ->collapsed()
                            ->compact()
                            ->deletable(false)
                            ->live()
                            ->relationship('userHasmanyAnotherDoc')
                            ->schema([
                                TextInput::make('doc_type')
                                    ->label('ประเภทเอกสาร')
                                    ->placeholder('ประเภทเอกสาร'),
                                DatePicker::make('date_of_issue')
                                    ->label('วันออกเอกสาร')
                                    ->placeholder('วันออกเอกสาร')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),
                                DatePicker::make('ate_of_expiry')
                                    ->label('วันเอกสารหมดอายุ')
                                    ->placeholder('วันเอกสารหมดอายุ')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),
                                Textarea::make('data')
                                    ->label('รายละเอียด')
                                    ->autosize()
                                    ->columnSpan(3),
                            ]),
                        Section::make('เอกสารเพิ่มเติม')
                            ->id('another')
                            ->collapsible()

                            ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                            ->footer(
                                function ($component) {
                                    return [
                                        Action::make('file_another')
                                            ->label('อับเดตเอกสาร')
                                            ->action(function ($livewire, $component, $record) {

                                                $user_id = auth()->user()->id;
                                                $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม
                                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                                                $fileForSend = array_values(array_diff($data[$component->getId()], $doc->path ?? []));

                                                if (!empty($data[$component->getId()])) {
                                                    $record->userHasmanyDocEmp()->updateOrCreate(
                                                        ['file_name' => $component->getId()],
                                                        [
                                                            'user_id' => $record->id,
                                                            'file_name_th' => $component->getHeading(),
                                                            'path' => $data[$component->getId()],
                                                            'confirm' => $data['confirm'],
                                                        ]
                                                    );
                                                }

                                                ProcessAnotherEmpDocJob::dispatch(
                                                    $fileForSend,
                                                    User::find($user_id),
                                                    $component->getId(),
                                                    $component->getHeading()
                                                );
                                            }),
                                        DeleteAction::make($component->getHeading())
                                            ->label("เคลียร์ข้อมูล")
                                            ->requiresConfirmation()
                                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"{$component->getHeading()}\" ทั้งหมด")
                                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"{$component->getHeading()}\ รวมถึงไฟล์ด้วยใช่หรือไม่")
                                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                                            ->action(function ($record, $component) {
                                                //dump($component->getHeading());
                                                $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                                                if (!empty($doc)) {
                                                    $record->userHasmanyAnotherDoc()->delete();
                                                    Storage::disk('public')->delete($doc->path);
                                                    $doc->delete();
                                                    return redirect("/profile?tab={$component->getId()}::data::tab");
                                                }
                                            })
                                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                                    ];
                                }
                            )
                            ->schema([
                                AdvancedFileUpload::make('another')
                                    ->hiddenLabel()
                                    ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                                    ->disk('public')
                                    ->directory('emp_files')
                                    ->multiple()
                                    ->reorderable()
                                    ->openable()
                                    ->appendFiles()
                                    ->removeUploadedFileButtonPosition('right')
                                    ->pdfFitType(PdfViewFit::FIT)
                                    ->previewable(function () {
                                        return $this->isMobile ? 0 : 1;
                                    })
                                    ->panelLayout(function () {
                                        return $this->isMobile ? null : 'grid';
                                    })
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->afterStateHydrated(function ($component, $record) {
                                        $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                                        $component->state($doc ? $doc->path : null);
                                    })
                                    ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                                        $name = $file->getClientOriginalName();
                                        $extension = $file->getClientOriginalExtension();
                                        $userEmail = auth()->user()->email;
                                        return "{$userEmail}/{$component->getName()}/{$name}.{$extension}";
                                    })
                                    ->deleteUploadedFileUsing(function ($state, $record, $component) {

                                        $doc = $record->userHasmanyDocEmp()
                                            ->where('file_name', $component->getName())
                                            ->first();
                                        $path = $doc->path;

                                        $fileDelete = array_values(array_diff($path, $state));
                                        if (count($path) > 1) {
                                            Storage::disk('public')->delete($fileDelete[0]);
                                            $pathSuccess = array_values(array_diff($path, $fileDelete));
                                            $record->userHasmanyDocEmp()->updateOrCreate(
                                                ['file_name' => $component->getName()],
                                                ['path' => $pathSuccess]
                                            );
                                        } else {
                                            Storage::disk('public')->delete($path);
                                            $doc->delete();
                                        }
                                        $doc_another = $record->userHasmanyAnotherDoc()
                                            ->where('file_path', $fileDelete[0])
                                            ->first();
                                        if (!empty($doc_another)) {
                                            $doc_another->delete();
                                        }
                                        return redirect("/profile?tab={$component->getName()}::data::tab");
                                    }),
                                Toggle::make('confirm')
                                    ->label(new HtmlString($this->confirm))
                                    ->accepted()
                                    ->live()
                                    ->afterStateHydrated(function () {
                                        $this->updateStateInConfirm(false);
                                    })
                                    ->default(false)
                                    ->validationMessages([
                                        'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                                    ])

                                    ->afterStateUpdated(function ($state) {
                                        $this->updateStateInConfirm($state);
                                    }),
                            ]),


                    ])
            ])->columnSpanFull()->persistTabInQueryString();
    }

    public function getLayout(): string
    {
        return 'filament-panels::components.layout.index';
    }

    // protected function getRedirectUrl(): ?string
    // {
    //     return env('APP_URL');
    // }

}





// Tab::make('สมุดบัญชีธนาคาร')
                //     ->tabslug('bookbank')
                //     ->schema([
                //         Section::make('ข้อมูลทั่วไป')
                //             ->collapsed()
                //             //->label('ข้อมูลทั่วไป')
                //             ->columns(3)
                //             ->relationship('userHasoneBookbank')
                //             ->schema([
                //                 TextInput::make('name')
                //                     ->placeholder('กรอกหรือแก้ไขชื่อจริงถ้าข้อมูลผิดพลาด')
                //                     ->label('ชื่อบัญชี'),
                //                 TextInput::make('bank_name')
                //                     ->placeholder('กรอกหรือแก้ไข้ชื่อบัญชีธนาคาร')
                //                     ->label('ชื่อธนาคาร'),
                //                 TextInput::make('bank_id')
                //                     ->label('เลขที่บัญชี')
                //                     ->placeholder('กรอกหรือแก้ไขเลขที่บัญชีธนาคาร'),
                //             ]),
                //         FileUpload::make('bookbank')
                //             //->pdfPreviewHeight(400) // Customize preview height
                //             // ->pdfDisplayPage(1) // Set default page
                //             // ->pdfToolbar(true) // Enable toolbar
                //             // ->pdfZoomLevel(100) // Set zoom level
                //             // ->pdfFitType(PdfViewFit::FIT) // Set fit type
                //             // ->pdfNavPanes(true) // Enable navigation panes
                //             ->label('เลือกไฟล์')
                //             ->openable()
                //             ->deletable(false)
                //             ->multiple()
                //             ->disabled()
                //             //->panelLayout('grid')
                //             ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                //             ->disk('public')
                //             ->directory('emp_files')
                //             ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component, $state) {

                //                 $i = mt_rand(1000, 9000);
                //                 $extension = $file->getClientOriginalExtension();
                //                 $userEmail = auth()->user()->email;
                //                 return "{$userEmail}/$component->getName().{$extension}";
                //             })
                //             ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                //             ->afterStateHydrated(function ($component, $state) {
                //                 $user = auth()->user();
                //                 $doc = $user->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                //                 $component->state($doc ? $doc->path : null);
                //             })
                //             ->hidden(function ($component, $record) {
                //                 $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                //                 return $doc ? 0 : 1;
                //             })
                //     ]),
