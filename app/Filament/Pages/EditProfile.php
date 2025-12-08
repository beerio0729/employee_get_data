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
use App\Jobs\ProcessNoJsonEmpDocJob;
use Filament\Forms\Components\Field;
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
    public $current_tab;
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

    protected function getTelFormComponent(): Component
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
                Section::make('คลิกที่นี่เพื่อเปลี่ยน Email หรือ Password')
                    ->description('ท่านสามารถแก้ไขอีเมลหรือรหัสผ่านได้ หรือ จะไม่แก้ไขก็ได้')
                    ->hiddenLabel()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getTelFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])->columns(4)->collapsed(),
                //$this->getDataResume(),
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
            ->contained(false)
            ->tabs([
                Tab::make('Resume')
                    ->tabslug('resume')
                    ->extraAttributes(fn() => $this->isMobile ? ["style" => "padding: 20px 10px"] : [])
                    ->schema([
                        FileUpload::make('image_profile')
                            ->label('กรุณาอับโหลดรูปภาพ')
                            ->disk('public')
                            ->dehydrated(false)
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
                            ->columns(4)
                            ->relationship('userHasoneResume')
                            ->schema([
                                TextInput::make('prefix_name')
                                    ->label('คำนำหน้าชื่อ')
                                    ->placeholder('กรอกคำนำหน้าชื่อ'),
                                TextInput::make('name')
                                    ->label('ชื่อ')
                                    ->placeholder('กรอกชื่อ'),
                                TextInput::make('last_name')
                                    ->label('นามสกุล')
                                    ->placeholder('กรอกนามสกุล'),
                                DatePicker::make('date_of_birth')
                                    ->label('วัน/เดือน/ปี เกิด')
                                    ->placeholder('ระบุ วัน/เดือน/ปี เกิด')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->locale('th')
                                    ->buddhist(),
                                TextInput::make('nationality')
                                    ->label('สัญชาติ')
                                    ->placeholder('ระบุสัญชาติ'),
                                TextInput::make('tel')
                                    ->columnSpan(1)
                                    ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                                    ->mask('999-999-9999')
                                    ->label('เบอร์โทรศัพท์')
                                    ->tel()
                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                                Select::make('marital_status')
                                    ->label('สถานภาพสมรส')
                                    ->placeholder('สถานภาพสมรส')
                                    ->options(config('iconf.marital_status')),
                                TextInput::make('religion')
                                    ->label('ศาสนา')
                                    ->placeholder('ระบุศาสนาที่ท่านนับถือ'),
                                TextInput::make('height')
                                    ->label('ส่วนสูง')
                                    ->placeholder('ระบุส่วนสูง cm')
                                    ->postfix('cm'),
                                TextInput::make('weight')
                                    ->label('น้ำหนัก')
                                    ->placeholder('ระบุน้ำหนัก kg')
                                    ->postfix('kg'),
                            ])->collapsed(),
                        Section::make('ที่อยู่ปัจจุบัน')
                            ->description('กรุณากรอกที่อยู่ปัจจุบันที่ติดต่อได้ เพื่อการส่งเอกสารที่จำเป็นไปให้ท่านได้ถูกต้อง')
                            ->columns(4)
                            ->relationship('userHasoneResumeToLocation')
                            ->schema([
                                Toggle::make('same_id_card')
                                    ->label('ใช้ที่อยู่เดียวกับบัตรประชาชน')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $set('address', null);
                                            $set('province_id', null);
                                            $set('district_id', null);
                                            $set('subdistrict_id', null);
                                            $set('zipcode', null);
                                        }
                                    }),
                                Textarea::make('address')
                                    ->label('รายละเอียดที่อยู่')
                                    ->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                                    ->columnSpan(4),
                                Select::make('province_id')
                                    ->options(Provinces::pluck('name_th', 'id'))
                                    ->live()
                                    ->preload()
                                    ->label('จังหวัด')
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
                                    ->preload()
                                    ->label('อำเภอ')
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
                                    ->label('ตำบล')
                                    ->preload()
                                    ->placeholder('ตำบล')
                                    ->live()
                                    ->searchable()
                                    ->afterStateUpdated(function (Select $column, Set $set) {
                                        $state = $column->getState();
                                        $zipcode = Subdistricts::where('id', $state)->pluck('zipcode');
                                        $set('zipcode', Str::slug($zipcode));
                                    }),
                                TextInput::make('zipcode')
                                    ->live()
                                    ->label('รหัสไปรษณีย์')
                                    ->placeholder('รหัสไปรษณีย์')
                            ])->collapsed(),
                        /*Section::make('ประวัติการศึกษา')
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
                            ])->collapsed(),*/
                        Section::make('ประสบการณ์การทำงาน')
                            ->schema([
                                Repeater::make('experiences')
                                    ->hiddenLabel()
                                    ->addActionLabel('เพิ่ม "ประสบการณ์ทำงาน"')
                                    ->relationship('userHasmanyResumeToWorkExperiences')
                                    ->schema([
                                        Fieldset::make('details')
                                            ->label('รายละเอียดประสบการณ์ทำงาน')
                                            ->columns(2)
                                            ->contained(true)
                                            ->schema([
                                                TextInput::make('company')
                                                    ->label('บริษัทที่เคยทำงาน')
                                                    ->placeholder('กรอกชื่อบริษัท'),
                                                TextInput::make('position')
                                                    ->label('ตำแหน่ง')
                                                    ->placeholder('กรอกตำแหน่งเดิมที่เคยทำงาน'),
                                                TextInput::make('start')
                                                    ->label('ช่วงที่เริ่มทำงาน')
                                                    ->placeholder('เช่น ม.ค. 2540'),
                                                TextInput::make('last')
                                                    ->label('ช่วงที่ลาออก')
                                                    ->placeholder('เช่น ธ.ค. 2545'),
                                                TextInput::make('salary')
                                                    ->label('เงินเดือน')
                                                    ->placeholder('เงินเดือนที่เคยได้รับ'),
                                                TextInput::make('reason_for_leaving')
                                                    ->label('สาเหตุที่ลาออก')
                                                    ->placeholder('กรอกสาเหตุที่ลาออก'),
                                                TextArea::make('details')
                                                    ->label('รายละเอียดเนื้องาน')
                                                    ->placeholder('กรอกรายละเอียดเนื้องานที่รับผิดชอบโดยสรุป')
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ])->collapsed(),
                        // Section::make('ไฟล์เอกสารเรซูเม่')
                        //     ->id('resume')
                        //     ->collapsible()
                        //     ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                        //     ->footer(
                        //         function ($component) {
                        //             return [
                        //                 Action::make('file_resume')
                        //                     ->tooltip('ใช้เฉพาะการอับเดตเอกสารเท่านั้น ไม่เกี่ยวกับการแก้ไขข้อมูลในฟอร์ม')
                        //                     ->hidden(fn($get) => !$get($component->getId() . 'confirm'))
                        //                     ->label("อับเดตเอกสาร" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()))
                        //                     ->action(function ($livewire, $component, $record) {
                        //                         $user_id = auth()->user()->id;
                        //                         $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม
                        //                         if (!empty($data[$component->getId()])) {
                        //                             $record->userHasmanyDocEmp()->updateOrCreate(
                        //                                 ['file_name' => $component->getId()],
                        //                                 [
                        //                                     'user_id' => $record->id,
                        //                                     'file_name_th' => str_replace('ไฟล์เอกสาร', '', $component->getHeading()),
                        //                                     'path' => $data[$component->getId()],
                        //                                     'confirm' => $data[$component->getId() . 'confirm'],
                        //                                 ]
                        //                             );
                        //                         }
                        //                         ProcessEmpDocJob::dispatch(
                        //                             $data[$component->getId()],
                        //                             User::find($user_id),
                        //                             $component->getId(),
                        //                             str_replace('ไฟล์เอกสาร', '', $component->getHeading())
                        //                         );
                        //                     }),
                        //                 DeleteAction::make($component->getHeading())
                        //                     ->label("เคลียร์ข้อมูล" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()) . "ทั้งหมด")
                        //                     ->requiresConfirmation()
                        //                     ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()) . "\" ทั้งหมด")
                        //                     ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()) . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                        //                     ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                        //                     ->action(function ($record, $component) {
                        //                         //dump($component->getHeading());
                        //                         $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();


                        //                         // 3.1 HasOne Relations (Location, JobPreference)
                        //                         $record->userHasOneResumeToLocation()->delete();
                        //                         $record->userHasOneResumeToJobPreference()->delete();

                        //                         // 3.2 HasMany Relations (Education, Work Experiences, etc.)
                        //                         $record->userHasManyResumeToEducation()->delete();
                        //                         $record->userHasManyResumeToWorkExperiences()->delete();
                        //                         $record->userHasManyResumeToLangSkill()->delete();
                        //                         $record->userHasManyResumeToSkill()->delete();
                        //                         $record->userHasManyResumeToCertificate()->delete();
                        //                         $record->userHasManyResumeToOtherContact()->delete();

                        //                         $record->userHasOneResume()->update([
                        //                             'prefix_name' => null,      // คำนำหน้าชื่อ
                        //                             'name' => null,             // ชื่อ
                        //                             'last_name' => null,        // นามสกุล
                        //                             'tel' => null,              // เบอร์โทรศัพท์
                        //                             'date_of_birth' => null,    // วัน/เดือน/ปี เกิด
                        //                             'marital_status' => null,   // สถานภาพสมรส
                        //                             'id_card' => null,          // เลขบัตรประชาชน
                        //                             'gender' => null,           // เพศ
                        //                             'height' => null,           // ส่วนสูง
                        //                             'weight' => null,           // น้ำหนัก
                        //                             'military' => null,         // เกณฑ์ทหาร
                        //                             'nationality' => null,      // สัญชาติ
                        //                             'religion' => null,         // ศาสนา
                        //                         ]);
                        //                         if (!empty($doc)) {
                        //                             Storage::disk('public')->delete($doc->path);
                        //                             $doc->delete();
                        //                         }
                        //                         return redirect("/profile?tab={$component->getId()}::data::tab");
                        //                     })
                        //                     ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                        //             ];
                        //         }
                        //     )
                        //     ->schema([
                        //         AdvancedFileUpload::make('resume')
                        //             ->hiddenLabel()
                        //             ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        //             ->disk('public')
                        //             ->directory('emp_files')
                        //             ->reorderable()
                        //             ->openable()
                        //             ->reactive()
                        //             ->appendFiles()
                        //             ->removeUploadedFileButtonPosition('right')
                        //             ->pdfFitType(PdfViewFit::FIT)
                        //             ->previewable(function () {
                        //                 return $this->isMobile ? 0 : 1;
                        //             })
                        //             ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        //             ->afterStateHydrated(function ($component, $record) {
                        //                 $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                        //                 $component->state($doc ? $doc->path : null);
                        //             })
                        //             ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                        //                 $i = mt_rand(1000, 9000);
                        //                 $extension = $file->getClientOriginalExtension();
                        //                 $userEmail = auth()->user()->email;
                        //                 return "{$userEmail}/{$component->getName()}/{$component->getName()}_{$i}.{$extension}";
                        //             })
                        //             ->deleteUploadedFileUsing(function ($record, $component) {
                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', $component->getName())
                        //                     ->first();
                        //                 if ($doc) {
                        //                     $path = $doc->path;
                        //                     Storage::disk('public')->delete($path);
                        //                     $doc->delete();


                        //                     // 3.1 HasOne Relations (Location, JobPreference)
                        //                     $record->userHasOneResumeToLocation()->delete(); // ต้องเรียกเมธอดที่สร้าง Relation
                        //                     $record->userHasOneResumeToJobPreference()->delete();

                        //                     // 3.2 HasMany Relations (Education, Work Experiences, etc.)
                        //                     $record->userHasManyResumeToEducation()->delete();
                        //                     $record->userHasManyResumeToWorkExperiences()->delete();
                        //                     $record->userHasManyResumeToLangSkill()->delete();
                        //                     $record->userHasManyResumeToSkill()->delete();
                        //                     $record->userHasManyResumeToCertificate()->delete();
                        //                     $record->userHasManyResumeToOtherContact()->delete();

                        //                     $record->userHasOneResume()->update([
                        //                         'prefix_name' => null,      // คำนำหน้าชื่อ
                        //                         'name' => null,             // ชื่อ
                        //                         'last_name' => null,        // นามสกุล
                        //                         'tel' => null,              // เบอร์โทรศัพท์
                        //                         'date_of_birth' => null,    // วัน/เดือน/ปี เกิด
                        //                         'marital_status' => null,   // สถานภาพสมรส
                        //                         'id_card' => null,          // เลขบัตรประชาชน
                        //                         'gender' => null,           // เพศ
                        //                         'height' => null,           // ส่วนสูง
                        //                         'weight' => null,           // น้ำหนัก
                        //                         'military' => null,         // เกณฑ์ทหาร
                        //                         'nationality' => null,      // สัญชาติ
                        //                         'religion' => null,         // ศาสนา
                        //                     ]);
                        //                 }
                        //                 redirect("/profile?tab={$component->getName()}");
                        //             }),
                        //         Toggle::make('resumeconfirm')
                        //             ->label(new HtmlString($this->confirm))
                        //             ->accepted()
                        //             ->live()
                        //             ->afterStateHydrated(function ($record, $component) {
                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', str_replace('confirm', '', $component->getName()))->first();
                        //                 $component->state(!empty($doc) ? $doc->confirm : 0);
                        //             })
                        //             ->default(false)
                        //             ->validationMessages([
                        //                 'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        //             ])

                        //     ]),

                    ]),





                Tab::make('idcard')
                    ->extraAttributes(fn() => $this->isMobile ? ["style" => "padding: 20px 10px"] : [])
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
                                    ->readonly() // ทำให้เป็นแบบอ่านอย่างเดียว
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
                        // Section::make('ไฟล์เอกสารบัตรประชาชน') //fileupload
                        //     ->id('idcard')
                        //     ->collapsible()
                        //     ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                        //     ->footer(
                        //         function ($component) {
                        //             return [
                        //                 Action::make('file_idcard')
                        //                     ->tooltip('ใช้เฉพาะการอับเดตเอกสารเท่านั้น ไม่เกี่ยวกับการแก้ไขข้อมูลในฟอร์ม')
                        //                     ->hidden(fn($get) => !$get('confirm'))
                        //                     ->label("อับเดตเอกสาร" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()))
                        //                     ->action(function ($livewire, $component, $record) {

                        //                         $user_id = auth()->user()->id;
                        //                         $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม

                        //                         if (!empty($data[$component->getId()])) {
                        //                             $record->userHasmanyDocEmp()->updateOrCreate(
                        //                                 ['file_name' => $component->getId()],
                        //                                 [
                        //                                     'user_id' => $record->id,
                        //                                     'file_name_th' => str_replace('ไฟล์เอกสาร', '', $component->getHeading()),
                        //                                     'path' => $data[$component->getId()],
                        //                                     'confirm' => $data['confirm'],
                        //                                 ]
                        //                             );
                        //                         }
                        //                         ProcessEmpDocJob::dispatch(
                        //                             $data[$component->getId()],
                        //                             User::find($user_id),
                        //                             $component->getId(),
                        //                             str_replace('ไฟล์เอกสาร', '', $component->getHeading())
                        //                         );
                        //                     }),
                        //                 DeleteAction::make($component->getHeading())
                        //                     ->label("เคลียร์ข้อมูล" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()) . "ทั้งหมด")
                        //                     ->requiresConfirmation()
                        //                     ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()) . "\" ทั้งหมด")
                        //                     ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . str_replace('ไฟล์เอกสาร', '', $component->getHeading()) . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                        //                     ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                        //                     ->action(function ($record, $component) {
                        //                         $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                        //                         $record->userHasOneIdcard()->delete();
                        //                         if (!empty($doc)) {
                        //                             Storage::disk('public')->delete($doc->path);
                        //                             $doc->delete();
                        //                         }
                        //                         return redirect("/profile?tab={$component->getId()}::data::tab");
                        //                     })
                        //                     ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                        //             ];
                        //         }
                        //     )
                        //     ->schema([
                        //         AdvancedFileUpload::make('idcard')
                        //             ->hiddenLabel()
                        //             ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        //             ->disk('public')
                        //             ->dehydrated(false)
                        //             ->directory('emp_files')
                        //             ->reorderable()
                        //             ->openable()
                        //             ->appendFiles()
                        //             ->removeUploadedFileButtonPosition('right')
                        //             ->pdfFitType(PdfViewFit::FIT)
                        //             ->previewable(function () {
                        //                 return $this->isMobile ? 0 : 1;
                        //             })
                        //             ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        //             ->afterStateHydrated(function ($component, $record) {
                        //                 $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                        //                 $component->state($doc ? $doc->path : null);
                        //                 $this->updateStateInConfirm(true);
                        //             })
                        //             ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                        //                 $i = mt_rand(1000, 9000);
                        //                 $extension = $file->getClientOriginalExtension();
                        //                 $userEmail = auth()->user()->email;
                        //                 return "{$userEmail}/{$component->getName()}/{$component->getName()}_{$i}.{$extension}";
                        //             })
                        //             ->deleteUploadedFileUsing(function ($state, $record, $component) {

                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', $component->getName())
                        //                     ->first();
                        //                 $path = $doc->path;
                        //                 $record->userHasOneIdcard()->delete();
                        //                 if (!empty($doc)) {
                        //                     Storage::disk('public')->delete($path);
                        //                     $doc->delete();
                        //                 }
                        //                 return redirect("/profile?tab={$component->getName()}");
                        //             }),
                        //         Toggle::make('idcardconfirm')
                        //             ->label(new HtmlString($this->confirm))
                        //             ->accepted()
                        //             ->live()
                        //             ->afterStateHydrated(function ($record, $component) {
                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', str_replace('confirm', '', $component->getName()))->first();
                        //                 $component->state(!empty($doc) ? $doc->confirm : 0);
                        //             })
                        //             ->default(false)
                        //             ->validationMessages([
                        //                 'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        //             ])
                        //     ]),
                    ]),
                Tab::make('วุฒิการศึกษา')
                    ->extraAttributes(fn() => $this->isMobile ? ["style" => "padding: 20px 10px"] : [])
                    ->tabslug('transcript')
                    ->schema([
                        Repeater::make('transcripts')
                            ->addable(false)
                            ->columns(3)
                            ->label('ข้อมูลเอกสารเพิ่มเติม')
                            ->itemLabel(fn(array $state): ?string => $state['degree'] ?? null)
                            ->collapsed()
                            ->compact()
                            ->deletable(false)
                            ->live()
                            ->relationship('userHasmanyTranscript')
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
                        // Section::make('วุฒิการศึกษา')
                        //     ->id('transcript')
                        //     ->collapsible()
                        //     ->contained(false)
                        //     ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                        //     ->footer(
                        //         function ($component) {
                        //             return [
                        //                 Action::make('file_transcript')
                        //                     ->label('อับเดตเอกสาร')
                        //                     ->action(function ($livewire, $component, $record) {
                        //                         $user_id = auth()->user()->id;
                        //                         $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม
                        //                         $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                        //                         $fileForSend = array_values(array_diff($data[$component->getId()], $doc->path ?? []));

                        //                         if (!empty($data[$component->getId()])) {
                        //                             $record->userHasmanyDocEmp()->updateOrCreate(
                        //                                 ['file_name' => $component->getId()],
                        //                                 [
                        //                                     'user_id' => $record->id,
                        //                                     'file_name_th' => $component->getHeading(),
                        //                                     'path' => $data[$component->getId()],
                        //                                     'confirm' => $data['confirm'],
                        //                                 ]
                        //                             );
                        //                         }

                        //                         ProcessNoJsonEmpDocJob::dispatch(
                        //                             $fileForSend,
                        //                             User::find($user_id),
                        //                             $component->getId(),
                        //                             $component->getHeading()
                        //                         );
                        //                     }),
                        //                 DeleteAction::make($component->getHeading())
                        //                     ->label("เคลียร์ข้อมูล{$component->getHeading()}")
                        //                     ->requiresConfirmation()
                        //                     ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"{$component->getHeading()}\" ทั้งหมด")
                        //                     ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"{$component->getHeading()}\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                        //                     ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                        //                     ->action(function ($record, $component) {
                        //                         //dump($component->getHeading());
                        //                         $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                        //                         $record->userHasmanyTranscript()->delete();
                        //                         if (!empty($doc)) {
                        //                             Storage::disk('public')->delete($doc->path);
                        //                             $doc->delete();
                        //                         }
                        //                         return redirect("/profile?tab={$component->getId()}::data::tab");
                        //                     })

                        //                     ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                        //             ];
                        //         }
                        //     )
                        //     ->schema([
                        //         AdvancedFileUpload::make('transcript')
                        //             ->hiddenLabel()
                        //             ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        //             ->disk('public')
                        //             ->dehydrated(false)
                        //             ->directory('emp_files')
                        //             ->multiple()
                        //             ->reorderable()
                        //             ->openable()
                        //             ->appendFiles()
                        //             ->removeUploadedFileButtonPosition('right')
                        //             ->pdfFitType(PdfViewFit::FIT)
                        //             ->previewable(function () {
                        //                 return $this->isMobile ? 0 : 1;
                        //             })
                        //             ->panelLayout(function () {
                        //                 return $this->isMobile ? null : 'grid';
                        //             })
                        //             ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        //             ->afterStateHydrated(function ($component, $record) {
                        //                 $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                        //                 $component->state($doc ? $doc->path : null);
                        //             })
                        //             ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                        //                 $i = mt_rand(1000, 9000);
                        //                 $extension = $file->getClientOriginalExtension();
                        //                 $userEmail = auth()->user()->email;
                        //                 return "{$userEmail}/{$component->getName()}/{$component->getName()}_{$i}.{$extension}";
                        //             })
                        //             ->deleteUploadedFileUsing(function ($state, $record, $component) {

                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', $component->getName())
                        //                     ->first();
                        //                 $path = $doc->path;

                        //                 $fileDelete = array_values(array_diff($path, $state));
                        //                 if (count($path) > 1) {
                        //                     Storage::disk('public')->delete($fileDelete[0]);
                        //                     $pathSuccess = array_values(array_diff($path, $fileDelete));
                        //                     $record->userHasmanyDocEmp()->updateOrCreate(
                        //                         ['file_name' => $component->getName()],
                        //                         ['path' => $pathSuccess]
                        //                     );
                        //                 } else {
                        //                     Storage::disk('public')->delete($path);
                        //                     $doc->delete();
                        //                 }
                        //                 $doc_transcript = $record->userHasmanyTranscript()
                        //                     ->where('file_path', $fileDelete[0])
                        //                     ->first();
                        //                 if (!empty($doc_transcript)) {
                        //                     $doc_transcript->delete();
                        //                 }
                        //                 return redirect("/profile?tab={$component->getName()}");
                        //             }),
                        //         Toggle::make('transcriptconfirm')
                        //             ->label(new HtmlString($this->confirm))
                        //             ->accepted()
                        //             ->live()
                        //             ->afterStateHydrated(function ($record, $component) {
                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', str_replace('confirm', '', $component->getName()))->first();
                        //                 $component->state(!empty($doc) ? $doc->confirm : 0);
                        //             })
                        //             ->default(false)
                        //             ->validationMessages([
                        //                 'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        //             ])
                        //     ]),
                    ]),

                Tab::make('เอกสารเพิ่มเติม')
                    ->extraAttributes(fn() => $this->isMobile ? ["style" => "padding: 20px 10px"] : [])
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
                        // Section::make('เอกสารเพิ่มเติม')
                        //     ->id('another')
                        //     ->collapsible()
                        //     ->contained(false)
                        //     ->description('ท่านสามารถลบเอกสาร และข้อมูลด้วยการคลิกที่ "X" ด้านขวาของเอกสารนั้น')
                        //     ->footer(
                        //         function ($component) {
                        //             return [
                        //                 Action::make('file_another')
                        //                     ->label('อับเดตเอกสาร')
                        //                     ->action(function ($livewire, $component, $record) {

                        //                         $user_id = auth()->user()->id;
                        //                         $data = $livewire->form->getState(); //ดึงค่า data จากฟอร์ม
                        //                         $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                        //                         $fileForSend = array_values(array_diff($data[$component->getId()], $doc->path ?? []));
                        //                         if (!empty($data[$component->getId()])) {
                        //                             $record->userHasmanyDocEmp()->updateOrCreate(
                        //                                 ['file_name' => $component->getId()],
                        //                                 [
                        //                                     'user_id' => $record->id,
                        //                                     'file_name_th' => $component->getHeading(),
                        //                                     'path' => $data[$component->getId()],
                        //                                     'confirm' => $data['confirm'],
                        //                                 ]
                        //                             );
                        //                         }

                        //                         ProcessNoJsonEmpDocJob::dispatch(
                        //                             $fileForSend,
                        //                             User::find($user_id),
                        //                             $component->getId(),
                        //                             $component->getHeading()
                        //                         );
                        //                     }),
                        //                 DeleteAction::make($component->getHeading())
                        //                     ->label("เคลียร์ข้อมูล{$component->getHeading()}")
                        //                     ->requiresConfirmation()
                        //                     ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"{$component->getHeading()}\" ทั้งหมด")
                        //                     ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"{$component->getHeading()}\ รวมถึงไฟล์ด้วยใช่หรือไม่")
                        //                     ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                        //                     ->action(function ($record, $component) {
                        //                         //dump($component->getHeading());
                        //                         $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getId())->first();
                        //                         $record->userHasmanyAnotherDoc()->delete();
                        //                         if (!empty($doc)) {
                        //                             Storage::disk('public')->delete($doc->path);
                        //                             $doc->delete();
                        //                         }
                        //                         return redirect("/profile?tab={$component->getId()}::data::tab");
                        //                     })
                        //                     ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                        //             ];
                        //         }
                        //     )
                        //     ->schema([
                        //         AdvancedFileUpload::make('another')
                        //             ->hiddenLabel()
                        //             ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        //             ->disk('public')
                        //             ->dehydrated(false)
                        //             ->directory('emp_files')
                        //             ->multiple()
                        //             ->reorderable()
                        //             ->openable()
                        //             ->appendFiles()
                        //             ->removeUploadedFileButtonPosition('right')
                        //             ->pdfFitType(PdfViewFit::FIT)
                        //             ->previewable(function () {
                        //                 return $this->isMobile ? 0 : 1;
                        //             })
                        //             ->panelLayout(function () {
                        //                 return $this->isMobile ? null : 'grid';
                        //             })
                        //             ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        //             ->afterStateHydrated(function ($component, $record) {
                        //                 $doc = $record->userHasmanyDocEmp()->where('file_name', $component->getName())->first();
                        //                 $component->state($doc ? $doc->path : null);
                        //             })
                        //             ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $component) {
                        //                 $name = $file->getClientOriginalName();
                        //                 $extension = $file->getClientOriginalExtension();
                        //                 $userEmail = auth()->user()->email;
                        //                 return "{$userEmail}/{$component->getName()}/{$name}.{$extension}";
                        //             })
                        //             ->deleteUploadedFileUsing(function ($state, $record, $component) {

                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', $component->getName())
                        //                     ->first();
                        //                 $path = $doc->path;

                        //                 $fileDelete = array_values(array_diff($path, $state));
                        //                 if (count($path) > 1) {
                        //                     Storage::disk('public')->delete($fileDelete[0]);
                        //                     $pathSuccess = array_values(array_diff($path, $fileDelete));
                        //                     $record->userHasmanyDocEmp()->updateOrCreate(
                        //                         ['file_name' => $component->getName()],
                        //                         ['path' => $pathSuccess]
                        //                     );
                        //                 } else {
                        //                     Storage::disk('public')->delete($path);
                        //                     $doc->delete();
                        //                 }
                        //                 $doc_another = $record->userHasmanyAnotherDoc()
                        //                     ->where('file_path', $fileDelete[0])
                        //                     ->first();
                        //                 if (!empty($doc_another)) {
                        //                     $doc_another->delete();
                        //                 }
                        //                 return redirect("/profile?tab={$component->getName()}");
                        //             }),
                        //         Toggle::make('anotherconfirm')
                        //             ->label(new HtmlString($this->confirm))
                        //             ->accepted()
                        //             ->live()
                        //             ->afterStateHydrated(function ($record, $component) {
                        //                 $doc = $record->userHasmanyDocEmp()
                        //                     ->where('file_name', str_replace('confirm', '', $component->getName()))->first();
                        //                 $component->state(!empty($doc) ? $doc->confirm : 0);
                        //             })
                        //             ->default(false)
                        //             ->validationMessages([
                        //                 'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        //             ])
                        //     ]),


                    ])
            ])->columnSpanFull()->persistTabInQueryString();
    }

    public function getLayout(): string
    {
        return 'filament-panels::components.layout.index';
    }

    function getActiveTabName()
    {
        $url = request()->header('Referer');
        $query = parse_url($url, PHP_URL_QUERY);
        $tabName = str_replace('tab=', '', $query);
        $this->current_tab = $tabName;
    }
}