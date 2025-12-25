<?php

namespace App\Filament\Components;

use Carbon\Carbon;
use App\Models\Geography\Districts;
use App\Models\Geography\Provinces;
use Detection\MobileDetect;
use Illuminate\Support\Str;
use App\Models\Geography\Subdistricts;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class UserFormComponent
{
    public bool $isMobile;
    public bool $isAndroidOS;

    public function __construct()
    {
        $detect = new MobileDetect();
        $this->isMobile = $detect->isMobile();
        $this->isAndroidOS = $detect->isAndroidOS();
    }

    public function fieldsetMalitalLabel($state)
    {
        $text = "ข้อมูลคู่สมรส";
        $icon = "⚠️"; // หรือ SVG icon
        $warning = "<div style='color: #FFA500; font-weight: bold;'>{$icon} คุณยังไม่ได้กรอกข้อมูลคู่สมรส</div>";
        return empty($state['alive']) ? $text . $warning : $text;
    }

    public function getDocEmp($record, $namedoc)
    {
        return blank($record) ? null : $record->userHasmanyDocEmp()->where('file_name', $namedoc);
    }

    /**********ส่วนของ Components************/

    public function idcardComponent($record, $namedoc)
    {
        return [
            Section::make('ข้อมูลจากบัตรประชาชน')
                ->hidden(function () use ($record, $namedoc) {
                    if (blank($record)) {
                        return 0;
                    } else {
                        $doc = $this->getDocEmp($record, $namedoc);
                        return $doc->exists() ? 0 : 1;
                    }
                })
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
                            return blank($get('date_of_birth'))
                                ? 'ต้องกรอกวันเกิดเพื่อคำนวณอายุ'
                                : Carbon::parse($get('date_of_birth'))->age;
                        })
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

                ]),
            Section::make('ที่อยู่ตามบัตรประชาชน')
                ->hidden(function () use ($record, $namedoc) {
                    if (blank($record)) {
                        return 0;
                    } else {
                        $doc = $this->getDocEmp($record, $namedoc);
                        return $doc->exists() ? 0 : 1;
                    }
                })
                ->collapsed()
                ->columns(3)
                ->relationship('userHasoneIdcard')
                ->schema([
                    Textarea::make('address')
                        ->hiddenlabel()
                        ->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                        ->columnSpan(3)
                        ->autosize()
                        ->trim(),
                    Select::make('province_id')
                        ->options(Provinces::pluck('name_th', 'id'))
                        ->live()
                        ->preload()
                        ->hiddenlabel()
                        ->placeholder('จังหวัด')
                        ->searchable()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state == null) {
                                $set('province_id', null);
                                $set('district_id', null);
                                $set('subdistrict_id', null);
                                $set('zipcode', null);
                            }
                        }),
                    Select::make('district_id')
                        ->options(function ($get) {
                            $data = Districts::where('province_id', $get('province_id'))
                                ->pluck('name_th', 'id');
                            return $data;
                        })
                        ->live()
                        ->preload()
                        ->hiddenlabel()
                        ->placeholder('อำเภอ')
                        ->searchable()
                        ->afterStateUpdated(function ($set) {
                            $set('subdistrict_id', null);
                            $set('zipcode', null);
                        }),
                    Select::make('subdistrict_id')
                        ->options(function ($get) {
                            $data = Subdistricts::where('district_id', $get('district_id'))
                                ->pluck('name_th', 'id');
                            return $data;
                        })
                        ->hiddenlabel()
                        ->preload()
                        ->placeholder('ตำบล')
                        ->live()
                        ->searchable()
                        ->afterStateUpdated(function ($state, $set) {
                            $zipcode = Subdistricts::where('id', $state)->pluck('zipcode'); //ไปที่ Subdistrict โดยที่ id = ปัจจุบันที่เราเลือก
                            $set('zipcode', Str::slug($zipcode)); //เอาค่าที่ได้ซึ่งเป็นอาเรย์มาถอดให้เหลือค่าอย่างเดียวด้วย Str::slug()แล้วเอาค่าที่ได้มาใส่ และส่งค่าไปยัง ฟิลด์ที่เลือกในที่นี้คือ zipcode
                        }),
                    TextInput::make('zipcode')
                        ->live()
                        ->hiddenlabel()
                        ->placeholder('รหัสไปรษณีย์')
                ]),

        ];
    }

    public function resumeComponent($record, $namedoc)
    {
        return
            Tabs::make('Tabs')
            ->persistTab()
            ->hidden(function () use ($record, $namedoc) {
                if (blank($record)) {
                    return 0;
                } else {
                    $doc = $this->getDocEmp($record, $namedoc);
                    return $doc->exists() ? 0 : 1;
                }
            })
            ->tabs([
                Tab::make('ข้อมูลเรซูเม่ทั่วไป')
                    ->extraAttributes(
                        fn() => ($this->isMobile)
                            ? ['style' => 'padding: 24px 15px']
                            : []
                    )
                    ->schema([
                        Section::make('ข้อมูลทั่วไป')
                            ->columns(3)
                            ->relationship('userHasoneResume')
                            ->contained(false)
                            ->hiddenLabel()
                            ->description('แสดงรายละเอียดข้อมูลทั่วไปจาก "เรซูเม่" โปรดตรวจสอบข้อมูลให้ถูกต้อง')
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
                                TextInput::make('tel')
                                    ->columnSpan(1)
                                    ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                                    ->mask('999-999-9999')
                                    ->label('เบอร์โทรศัพท์')
                                    ->tel()
                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                                TextInput::make('height')
                                    ->label('ส่วนสูง')
                                    ->placeholder('ระบุส่วนสูง cm')
                                    ->postfix('cm'),
                                TextInput::make('weight')
                                    ->label('น้ำหนัก')
                                    ->placeholder('ระบุน้ำหนัก kg')
                                    ->postfix('kg'),
                            ])->collapsed()
                    ]),
                Tab::make('ที่อยู่ปัจจุบัน')
                    ->extraAttributes(
                        fn() => ($this->isMobile)
                            ? ['style' => 'padding: 24px 15px']
                            : []
                    )
                    ->schema([
                        Section::make('ที่อยู่ปัจจุบัน')
                            ->description('แสดงที่อยู่ปัจจุบันที่ติดต่อได้ เพื่อการส่งเอกสารที่จำเป็นไปให้ท่านได้ถูกต้อง')
                            ->contained(false)
                            ->columns(4)
                            ->relationship('userHasoneResumeToLocation')
                            ->schema([
                                Toggle::make('same_id_card')
                                    ->label('ใช้ที่อยู่เดียวกับบัตรประชาชน')
                                    ->live(),
                                // ->afterStateUpdated(function ($state, $set) {
                                //     if ($state) {
                                //         $set('address', null);
                                //         $set('province_id', null);
                                //         $set('district_id', null);
                                //         $set('subdistrict_id', null);
                                //         $set('zipcode', null);
                                //     }
                                // }),
                                Textarea::make('address')
                                    ->hidden(fn($get) => $get('same_id_card') ? 1 : 0)
                                    ->label('รายละเอียดที่อยู่')
                                    ->placeholder('กรุณากรอกรายละเอียดที่อยู่ให้ละเอียดที่สุด')
                                    ->columnSpan(4)
                                    ->autosize()
                                    ->trim(),
                                Select::make('province_id')
                                    ->hidden(fn($get) => $get('same_id_card') ? 1 : 0)
                                    ->options(Provinces::pluck('name_th', 'id'))
                                    ->live()
                                    ->preload()
                                    ->label('จังหวัด')
                                    ->placeholder('จังหวัด')
                                    ->searchable()
                                    ->afterStateUpdated(function ($state, $set) {

                                        if ($state == null) {
                                            $set('province_id', null);
                                            $set('district_id', null);
                                            $set('subdistrict_id', null);
                                            $set('zipcode', null);
                                        }
                                    }),
                                Select::make('district_id')
                                    ->hidden(fn($get) => $get('same_id_card') ? 1 : 0)
                                    ->options(function (Get $get) {
                                        $data = Districts::where('province_id', $get('province_id'))
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
                                    ->hidden(fn($get) => $get('same_id_card') ? 1 : 0)
                                    ->options(function (Get $get) {
                                        $data = Subdistricts::where('district_id', $get('district_id'))
                                            ->pluck('name_th', 'id');
                                        return $data;
                                    })
                                    ->label('ตำบล')
                                    ->preload()
                                    ->placeholder('ตำบล')
                                    ->live()
                                    ->searchable()
                                    ->afterStateUpdated(function ($state, $set) {

                                        $zipcode = Subdistricts::where('id', $state)->pluck('zipcode');
                                        $set('zipcode', Str::slug($zipcode));
                                    }),
                                TextInput::make('zipcode')
                                    ->hidden(fn($get) => $get('same_id_card') ? 1 : 0)
                                    ->live()
                                    ->label('รหัสไปรษณีย์')
                                    ->placeholder('รหัสไปรษณีย์')
                            ])->collapsed(),
                    ]),
                Tab::make('ตำแหน่งงาน')
                    ->extraAttributes(
                        fn() => ($this->isMobile)
                            ? ['style' => 'padding: 24px 15px']
                            : []
                    )
                    ->schema([
                        Section::make('ตำแหน่งงาน')
                            ->contained(false)
                            ->relationship('userHasoneResumeToJobPreference')
                            ->description('ระบุตำแหน่งงานทีต้องการสมัคร ได้สูงสุด 4 ตำแหน่ง/ รวมถึงเลือกพื้นที่ทำงาน')
                            ->schema([
                                Fieldset::make('job_con')
                                    ->label('ความพร้อมในการทำงาน')
                                    ->extraAttributes(
                                        fn() => $this->isMobile
                                            ? ['style' => 'padding: 24px 10px']
                                            : []
                                    )
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('availability_date')
                                            ->label('ช่วงเวลาพร้อมเริ่มงาน'),
                                        TextInput::make('expected_salary')
                                            ->label('เงินเดือนที่ต้องการ')
                                    ]),
                                Fieldset::make('position_con')
                                    ->label('ตำแหน่งงงาน')
                                    ->extraAttributes(
                                        fn() => $this->isMobile
                                            ? ['style' => 'padding: 24px 10px']
                                            : []
                                    )
                                    ->schema([
                                        Repeater::make('position')
                                            ->hiddenLabel()
                                            ->maxItems(4)
                                            ->columnSpanFull()
                                            ->grid(fn($state) => count($state) < 4 ? count($state) : 4)
                                            ->addActionLabel('เพิ่ม "ตำแหน่งงาน"')
                                            ->itemNumbers()
                                            ->afterStateUpdated(function (array $state, $record) {
                                                $datas = array_map(fn($item) => $item['position'], $state);

                                                if (count($datas) === count($record?->position ?? [])) {
                                                    $record->updateOrCreate(
                                                        ['resume_id' => $record->resume_id],            // เงื่อนไขหาแถวเดิม
                                                        ['position' => array_values($datas)]   // ข้อมูลที่จะอัปเดตหรือสร้าง
                                                    );
                                                    Notification::make()
                                                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                                                        ->color('success')
                                                        ->send();
                                                }
                                            })
                                            ->simple(
                                                TextInput::make('position')
                                                    ->label('ตำแหน่งงาน')
                                                    ->placeholder('ระบุตำแหน่งงานที่ต้องการ')
                                                    ->afterStateHydrated(function ($component, $state) {
                                                        if (! blank($state)) {
                                                            // แปลงเฉพาะตอนแสดงใน input
                                                            $component->state(ucwords($state));
                                                        }
                                                    }),

                                            )
                                            ->columnSpanFull(),
                                    ]),
                                Fieldset::make('location_con')
                                    ->columns(4)
                                    ->label('พื้นที่ทำงาน')
                                    ->extraAttributes(
                                        fn() => $this->isMobile
                                            ? ['style' => 'padding: 24px 10px']
                                            : []
                                    )
                                    ->schema([
                                        Select::make('location')
                                            ->options(Provinces::orderBy('code')->pluck('name_th', 'id'))
                                            ->multiple()
                                            ->maxItems(4)
                                            ->searchable()
                                            ->label('ระบุจังหวัดที่ต้องการทำงาน')
                                            ->placeholder('เลือกจังหวัดได้มากกว่า 1 จังหวัด')
                                            ->columnSpanFull()
                                            ->searchPrompt('ท่านสามารถพิมพ์ค้นหาชื่อจังหวัดได้')
                                            ->noSearchResultsMessage('ไม่มีจังหวัดที่คุณค้นหา')
                                    ])->columnSpanFull(),


                            ])->collapsed(),
                    ]),
                Tab::make('ประสบการณ์ทำงาน')
                    ->extraAttributes(
                        fn() => ($this->isMobile)
                            ? ['style' => 'padding: 24px 15px']
                            : []
                    )
                    ->schema([
                        Section::make('ประสบการณ์ทำงาน')
                            ->description("แสดงข้อมูลประสบการณ์ทำงานของท่าน สามารถกรอกข้อมูลเพิ่มเติมได้")
                            ->contained(false)
                            ->schema([
                                Repeater::make('experiences')
                                    ->itemLabel(fn(array $state): ?string => $state['company'] ?? null)
                                    ->collapsed()
                                    ->columns(3)
                                    ->hiddenLabel()
                                    ->addActionLabel('เพิ่ม "ประสบการณ์ทำงาน"')
                                    ->relationship('userHasmanyResumeToWorkExperiences')
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
                                            ->columnSpanFull()
                                            ->autosize()
                                            ->trim(),
                                    ]),

                            ])->collapsed(),
                    ]),
                Tab::make('ความสามาถทางภาษา')
                    ->extraAttributes(
                        fn() => ($this->isMobile)
                            ? ['style' => 'padding: 24px 15px']
                            : []
                    )
                    ->schema([
                        Section::make('ความสามาถทางภาษา')
                            ->contained(false)
                            ->description("แสดงข้อมูลทักษะด้านภาษาของท่าน สามารถกรอกข้อมูลเพิ่มเติมได้")
                            ->schema([
                                Repeater::make('langskill')
                                    ->itemLabel(fn(array $state): ?string => $state['language'] ?? null)
                                    ->collapsed()
                                    ->columns(4)
                                    ->hiddenLabel()
                                    ->addActionLabel('เพิ่ม "ความสามารถทางภาษา"')
                                    ->relationship('userHasmanyResumeToLangSkill')
                                    ->schema([
                                        TextInput::make('language')
                                            ->label('ภาษา')
                                            ->placeholder('กรอกความสามารถทางภาษา')
                                            ->afterStateHydrated(function ($component, $state) {
                                                if (! blank($state)) {
                                                    // แปลงเฉพาะตอนแสดงใน input
                                                    $component->state(ucwords($state));
                                                }
                                            }),
                                        Select::make('speaking')
                                            ->options(Config('iconf.skill_level'))
                                            ->label('การพูด'),
                                        Select::make('listening')
                                            ->options(Config('iconf.skill_level'))
                                            ->label('การฟัง'),
                                        Select::make('writing')
                                            ->options(Config('iconf.skill_level'))
                                            ->label('การเขียน'),
                                    ]),

                            ])->collapsed(),
                    ]),
                Tab::make('ความสามาถด้านอื่นๆ')
                    ->extraAttributes(
                        fn() => ($this->isMobile)
                            ? ['style' => 'padding: 24px 15px']
                            : []
                    )
                    ->schema([
                        Section::make('ความสามาถด้านอื่นๆ')
                            ->contained(false)
                            ->description("แสดงข้อมูลทักษะด้านอื่นๆ ของท่าน สามารถกรอกข้อมูลเพิ่มเติมได้")
                            ->schema([
                                Repeater::make('skills')
                                    ->itemLabel(fn(array $state): ?string => $state['skill_name'] ?? null)
                                    ->collapsed()
                                    ->columns(2)
                                    ->hiddenLabel()
                                    ->addActionLabel('เพิ่ม "ความสามารถอื่นๆ"')
                                    ->relationship('userHasmanyResumeToSkill')
                                    ->schema([
                                        TextInput::make('skill_name')
                                            ->label('ภาษา')
                                            ->placeholder('กรอกความสามารถทางภาษา')
                                            ->afterStateHydrated(function ($component, $state) {
                                                if (! blank($state)) {
                                                    // แปลงเฉพาะตอนแสดงใน input
                                                    $component->state(ucwords($state));
                                                }
                                            }),
                                        Select::make('level')
                                            ->options(Config('iconf.skill_level'))
                                            ->label('ระดับความชำนาญ'),

                                    ]),

                            ])->collapsed(),
                    ]),

            ]);
    }

    public function transcriptComponent($record, $namedoc)
    {
        return
            Section::make('ข้อมูลวุฒิการศึกษา')
            ->hidden(function () use ($record, $namedoc) {
                if (blank($record)) {
                    return 0;
                } else {
                    $doc = $this->getDocEmp($record, $namedoc);
                    return $doc->exists() ? 0 : 1;
                }
            })
            ->description('มีโอกาสที่ Ai จะอ่านข้อมูลผิดพลาด โปรดตรวจสอบข้อมูลให้ถูกต้องตามจริง')
            ->collapsed()
            ->schema([
                Repeater::make('transcripts')
                    ->addable(false)
                    ->columns(3)
                    ->label('คลิกที่ชื่อวุฒิการศึกษาเพื่อดูข้อมูล')
                    ->itemLabel(fn(array $state): ?string => $state['degree'] ?? null)
                    ->collapsed()
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
                    ])
            ]);
    }

    public function militaryComponent($record, $namedoc) //ทหาร
    {
        return
            Section::make('ข้อมูลใบเกณฑ์หทาร')
            ->hidden(function () use ($record, $namedoc) {
                if (blank($record)) {
                    return 0;
                } else {
                    $doc = $this->getDocEmp($record, $namedoc);
                    return $doc->exists() ? 0 : 1;
                }
            })
            ->description('มีโอกาสที่ Ai จะอ่านข้อมูลผิดพลาดสูงมากเนื่องจากเป็นตัวอักษรเขียน โปรดตรวจสอบข้อมูลให้ถูกต้องตามจริง')
            ->columns(4)
            ->relationship('userHasoneMilitary')
            ->collapsed()
            ->schema([
                TextInput::make('id_card')
                    ->label('เลขบัตรประชาชน')
                    ->mask('9-9999-99999-99-9')
                    ->placeholder('รหัสบัตรประชาชน (กรอกเฉพาะตัวเลข)'),
                Select::make('type')
                    ->live()
                    ->label('เอกสาร สด.')
                    ->options([
                        8  => 'สด.8',
                        35 => 'สด.35',
                        43 => 'สด.43',
                    ]),
                Select::make('category')
                    ->live()
                    ->hidden(fn($get) => $get('type') === 8 ? 1 : 0)
                    ->label('ประเภทบุคคล')
                    ->options([
                        1  => 'เป็นบุคคลจำพวกที่ 1',
                        2 => 'เป็นบุคคลจำพวกที่ 2',
                        3 => 'เป็นบุคคลจำพวกที่ 3',
                        4 => 'เป็นบุคคลจำพวกที่ 4',
                    ]),
                Select::make('result')
                    ->live()
                    ->hidden(fn($get) => $get('type') === 8 ? 1 : 0)
                    ->label('ผลการตรวจเลือก')
                    ->options([
                        'ดำ'  => 'ใบดำ',
                        'แดง' => 'ใบแดง',
                        'ยกเว้น' => 'ได้รับการยกเว้น',
                    ]),
                TextInput::make('reason_for_exemption')
                    ->hidden(fn($get) => $get('result') === 'ยกเว้น' ? 0 : 1)
                    ->label('เหตุผลที่ได้รับการยกเว้น')
                    ->placeholder('ระบุเหตุผลที่ได้รับการยกเว้น')
                    ->columnSpan(2),
                DatePicker::make('date_to_army')
                    ->hidden(fn($get) => $get('result') === 'แดง' ? 0 : 1)
                    ->label('วันกำหนดรับราชการทหาร')
                    ->placeholder('วันกำหนดรับราชการทหาร')
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->locale('th')
                    ->buddhist(),

            ])->collapsed();
    }

    public function maritalComponent()
    {
        return
            Section::make('ข้อมูลการสมรส')
            ->description('หากมีเอกสารใบสมรส คุณจำเป็นต้องอับโหลดเพื่อรักษาสิทธิ์เกี่ยวกับคู่สมรสของคุณเอง')
            ->columns(4)
            ->relationship('userHasoneMarital')
            ->collapsed()
            ->collapsed()
            ->schema([
                Radio::make('status')
                    ->label('เลือกสถานะการแต่งงาน')
                    ->columnSpanFull()
                    ->columns(5)
                    ->live()
                    ->options([
                        'single' => 'โสด',
                        'married' => 'แต่งงานแล้ว',
                        'divorced' => 'หย่าร้าง',
                        'widowed' => 'เป็นหม้าย',
                        'separated' => 'แยกกันอยู่',
                    ]),
                Fieldset::make('info_from_doc')
                    ->hidden(
                        fn($get, $state) => ($get('status') === 'single' || blank($state['status'])) ? 1 : 0
                    )
                    ->label('ข้อมูลการสมรสจากเอกสาร')
                    ->extraAttributes(
                        fn() => $this->isMobile
                            ? ['style' => 'padding: 24px 10px']
                            : []
                    )
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Select::make('type')
                            ->live()
                            ->label('ประเภทเอกสาร')
                            ->options([
                                'married'  => 'ใบสำคัญการสมรส',
                                'divorced' => 'ใบสำคัญการหย่า',
                            ]),
                        TextInput::make('registration_number')
                            ->label('เลขที่ใบเอกสาร')
                            ->placeholder('ระบุเลขที่ใบเอกสาร'),
                        DatePicker::make('issue_date')
                            ->label('วันออกเอกสาร')
                            ->placeholder('ระบุวันออกเอกสาร')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->locale('th')
                            ->buddhist(),
                        TextInput::make('man')
                            ->label('ชื่อฝ่ายชาย')
                            ->placeholder('ระบุชื่อฝ่ายชาย'),
                        TextInput::make('woman')
                            ->label('ชื่อฝ่ายหญิง')
                            ->placeholder('ระบุชื่อฝ่ายหญิง'),
                    ]),
                Fieldset::make('info_of_spouse')
                    ->visible(fn($get) => $get('status') === 'married' ? 1 : 0)
                    ->label(fn($state) => new HtmlString($this->fieldsetMalitalLabel($state)))
                    ->extraAttributes(
                        fn() => $this->isMobile
                            ? ['style' => 'padding: 24px 10px']
                            : []
                    )
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('spouse')
                            ->label('ชื่อคู่สมรส')
                            ->readOnly()
                            ->afterStateHydrated(function ($set) {
                                $user = auth()->user();
                                $gender = $user->userHasoneIdcard->gender;

                                $spouseName = ($gender === 'male')
                                    ? $user->userHasoneMarital?->woman
                                    : $user->userHasoneMarital?->man;

                                $set('spouse', $spouseName);
                            }),
                        TextInput::make('age')
                            ->label('อายุ')
                            ->postfix('ปี'),
                        TextInput::make('occupation')
                            ->label('อาชีพ'),
                        TextInput::make('company')
                            ->label('บริษัท'),
                        TextInput::make('male')
                            ->live()
                            ->label('จำนวนบุตรชาย')
                            ->postfix('คน'),
                        TextInput::make('female')
                            ->live()
                            ->label('จำนวนบุตรสาว')
                            ->postfix('คน'),
                        Radio::make('alive')
                            ->label('ยังมีชีวิตอยู่หรือไม่?')
                            ->options([
                                true => 'ยังมีชีวิตอยู่',
                                false => 'เสียชีวิตแล้ว',
                            ])
                            ->inline(),

                    ]),
            ]);
    }

    public function certificateComponent($record, $namedoc)
    {
        return
            Section::make('ใบ Certificate')
            ->hidden(function () use ($record, $namedoc) {
                if (blank($record)) {
                    return 0;
                } else {
                    $doc = $this->getDocEmp($record, $namedoc);
                    return $doc->exists() ? 0 : 1;
                }
            })
            ->description('มีโอกาสที่ Ai จะอ่านข้อมูลผิดพลาด โปรดตรวจสอบข้อมูลให้ถูกต้องตามจริง')
            ->columns(4)
            ->relationship('userHasoneCertificate')
            ->collapsed()
            ->schema([
                Repeater::make('data')
                    ->addable(false)
                    ->columns(4)
                    ->hiddenLabel()
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                    ->collapsed()
                    ->columnSpanFull()
                    ->reorderable(false)
                    ->deletable(false)
                    ->live()
                    ->schema([
                        TextInput::make('name')
                            ->label('ชื่อคอร์สที่ฝึกอบรม')
                            ->placeholder('ระบุชื่อคอร์สที่เข้าอบรม'),
                        TextInput::make('institutes')
                            ->label('สถาบัน')
                            ->placeholder('ระบุสถาบันที่ได้รับได้อบรม'),
                        TextInput::make('duration')
                            ->label('ระยะเวลาในการอบรม')
                            ->placeholder('ระบุระยะเวลาในการอบรมเช่น 5 วัน'),
                        DatePicker::make('date')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->locale('th')
                            ->buddhist()
                            ->label('ปีที่ได้ใบประกาศนียบัตร')
                            ->placeholder('ระบุปีที่ได้ใบประกาศนียบัตร'),
                    ])
            ]);
    }

    public function anotherDocComponent($record, $namedoc)
    {
        return
            Section::make('ข้อมูลเอกสารเพิ่มเติม')
            ->hidden(function () use ($record, $namedoc) {
                if (blank($record)) {
                    return 0;
                } else {
                    $doc = $this->getDocEmp($record, $namedoc);
                    return $doc->exists() ? 0 : 1;
                }
            })
            ->description('มีโอกาสที่ Ai จะอ่านข้อมูลผิดพลาด โปรดตรวจสอบข้อมูลให้ถูกต้องตามจริง')
            ->collapsed()
            ->schema([
                Repeater::make('anothers')
                    ->addable(false)
                    ->columns(3)
                    ->label('คลิกดูข้อมูลเอกสารเพิ่มเติม')
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
                            ->trim()
                            ->columnSpan(3),
                    ])
            ]);
    }

    /*********ข้อมูลเพิ่มเติมที่ไม่มีเอกสาร***********/
    public function familyComponent()
    {
        return [
            Section::make('ข้อมูลบิดา')
                ->relationship('userHasoneFather')
                ->icon(
                    fn($state) => blank($state['name'])
                        ? 'heroicon-m-exclamation-triangle'
                        : 'heroicon-m-check-circle'
                )
                ->iconColor(fn($state) => blank($state['name'])
                    ? 'warning'
                    : 'success')
                ->description(
                    fn($state) => blank($state['name'])
                        ? 'คุณยังไม่ได้กรอกข้อมูลของบิดา กรุณากรอกข้อมูลให้ครบถ้วนตามจริง'
                        : null
                )
                ->collapsed()
                ->columns(3)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้กรอกชื่อบิดา'
                        ])
                        ->label('ชื่อ-นามสกุล บิดา')
                        ->placeholder('กรอกชื่อ-นามสกุล บิดา'),
                    TextInput::make('age')
                        ->label('อายุ')
                        ->placeholder('กรอกเฉพาะตัวเลข')
                        ->postfix('ปี'),
                    TextInput::make('nationality')
                        ->placeholder('ระบุสัญชาติตามจริง')
                        ->label('สัญชาติ'),
                    TextInput::make('occupation')
                        ->placeholder('กรอกข้อมูลอาชีพ')
                        ->label('อาชีพ'),
                    TextInput::make('company')
                        ->placeholder('ชื่อบริษัทที่ทำงาน(ถ้ามี)')
                        ->label('บริษัทที่ทำงาน (ถ้ามี)'),
                    TextInput::make('tel')
                        ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                        ->mask('999-999-9999')
                        ->label('เบอร์โทรติดต่อ')
                        ->tel()
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                    Radio::make('alive')
                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้เลือกตัวเลือกใดตัวเลือกหนึ่ง'
                        ])
                        ->label('ยังมีชีวิตอยู่หรือไม่?')
                        ->options([
                            true => 'ยังมีชีวิตอยู่',
                            false => 'เสียชีวิตแล้ว',
                        ])
                        ->inline(),
                ]),
            Section::make('ข้อมูลมารดา')
                ->relationship('userHasoneMother')
                ->icon(
                    fn($state) => blank($state['name'])
                        ? 'heroicon-m-exclamation-triangle'
                        : 'heroicon-m-check-circle'
                )
                ->iconColor(fn($state) => blank($state['name'])
                    ? 'warning'
                    : 'success')
                ->description(
                    fn($state) => blank($state['name'])
                        ? 'คุณยังไม่ได้กรอกข้อมูลของมารดา กรุณากรอกข้อมูลให้ครบถ้วนตามจริง'
                        : null
                )
                ->columns(3)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้กรอกชื่อมารดา'
                        ])
                        ->label('ชื่อ-นามสกุล มารดา')
                        ->placeholder('กรอกชื่อ-นามสกุล มารดา'),
                    TextInput::make('age')
                        ->label('อายุ')
                        ->placeholder('กรอกเฉพาะตัวเลข')
                        ->postfix('ปี'),
                    TextInput::make('nationality')
                        ->placeholder('ระบุสัญชาติตามจริง')
                        ->label('สัญชาติ'),
                    TextInput::make('occupation')
                        ->placeholder('กรอกข้อมูลอาชีพ')
                        ->label('อาชีพ'),
                    TextInput::make('company')
                        ->placeholder('ชื่อบริษัทที่ทำงาน(ถ้ามี)')
                        ->label('บริษัทที่ทำงาน (ถ้ามี)'),
                    TextInput::make('tel')
                        ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                        ->mask('999-999-9999')
                        ->label('เบอร์โทรติดต่อ')
                        ->tel()
                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                    Radio::make('alive')
                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้เลือกตัวเลือกใดตัวเลือกหนึ่ง'
                        ])
                        ->label('ยังมีชีวิตอยู่หรือไม่?')
                        ->options([
                            true => 'ยังมีชีวิตอยู่',
                            false => 'เสียชีวิตแล้ว',
                        ])
                        ->inline(),
                ])->collapsed(),
            Section::make('ข้อมูลพี่น้อง')
                ->description('กรุณากรอกข้อมูลเรียงตามลำดับพี่น้อง *รวมคุณด้วย* หากลำดับไหนคือคุณให้ต๊ิกเลือกได้เลย')
                ->relationship('userHasoneSibling')
                ->schema([
                    Repeater::make('data')
                        ->addActionLabel('เพิ่มข้อมูลพี่น้อง')
                        ->columns(3)
                        ->itemNumbers()
                        ->hiddenLabel()
                        ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                        ->collapsible()
                        ->columnSpanFull()
                        ->reorderable()
                        ->live()
                        ->afterStateUpdated(function (array $state, $record) {
                            $datas = array_values($state);
                            $old = $record?->data ?? [];
                            $new = $datas;

                            if (count($new) === count($old)) {
                                $record->updateOrCreate(
                                    ['user_id' => $record->user_id], // เงื่อนไขหาแถวเดิม
                                    ['data' => $new]   // ข้อมูลที่จะอัปเดตหรือสร้าง
                                );
                                Notification::make()
                                    ->title('แก้ไขข้อมูลแล้ว')
                                    ->success()
                                    ->send();
                            }
                            if (count($new) < count($old)) {
                                $record->updateOrCreate(
                                    ['user_id' => $record->user_id], // เงื่อนไขหาแถวเดิม
                                    ['data' => $new]   // ข้อมูลที่จะอัปเดตหรือสร้าง
                                );
                                Notification::make()
                                    ->title('ลบข้อมูลที่ต้องการเรียบร้อยแล้ว')
                                    ->color('danger')
                                    ->icon('heroicon-m-trash')
                                    ->send();
                            }
                        })
                        ->schema([
                            Toggle::make('you')
                                ->afterStateUpdated(
                                    function ($set, $state) {
                                        if ($state) {
                                            $set('name', 'This is Me.');
                                            $set('gender', auth()->user()->userHasoneIdcard?->gender);
                                        } else {
                                            $set('name', null);
                                            $set('gender', null);
                                        }
                                    }
                                )
                                ->columnSpanFull()
                                ->label('คลิกที่นี้ถ้าคุณคือลำดับนี้'),
                            TextInput::make('name')
                                ->readOnly(fn($get) => $get('you') ? 1 : 0)
                                ->label('ชื่อ-นามสกุล')
                                ->placeholder('กรอกชื่อ-นามสกุลพี่-น้อง'),
                            TextInput::make('age')
                                ->hidden(fn($get) => $get('you') ? 1 : 0)
                                ->label('อายุ')
                                ->placeholder('กรอกเฉพาะตัวเลข')
                                ->postfix('ปี'),
                            Select::make('gender')
                                //->hidden(fn($get) => $get('you') ? 1 : 0)
                                ->label('เพศ')
                                ->options([
                                    'male' => 'เพศชาย',
                                    'female' => 'เพศหญิง',
                                ]),
                            TextInput::make('occupation')
                                ->hidden(fn($get) => $get('you') ? 1 : 0)
                                ->placeholder('กรอกข้อมูลอาชีพ')
                                ->label('อาชีพ'),
                            TextInput::make('company')
                                ->hidden(fn($get) => $get('you') ? 1 : 0)
                                ->placeholder('ชื่อบริษัทที่ทำงาน(ถ้ามี)')
                                ->label('บริษัทที่ทำงาน (ถ้ามี)'),
                            TextInput::make('position')
                                ->hidden(fn($get) => $get('you') ? 1 : 0)
                                ->placeholder('ระบุตำแหน่งงานปัจจุบัน')
                                ->label('ตำแหน่งงาน (ถ้ามี)'),

                        ]),
                ])->collapsed(),
        ];
    }

    public function emergencyContactComponent()
    {
        return
            Section::make('ข้อมูลผู้ที่ติดต่อยามฉุกเฉิน')
            ->collapsed()
            ->icon(
                fn($state) => blank($state['emergency_name'])
                    ? 'heroicon-m-exclamation-triangle'
                    : 'heroicon-m-check-circle'
            )
            ->iconColor(fn($state) => blank($state['emergency_name'])
                ? 'warning'
                : 'success')
            ->description(
                fn($state) => blank($state['emergency_name'])
                    ? 'คุณยังไม่ได้กรอกข้อมูลผู้ที่ติดต่อยามฉุกเฉิน กรุณากรอกข้อมูลให้ครบถ้วนตามจริง'
                    : null
            )
            ->columns(3)
            ->relationship('userHasoneAdditionalInfo')
            ->schema([
                TextInput::make('emergency_name')
                    ->required()
                    ->validationMessages([
                        'required' => 'คุณยังไม่ได้กรอกชื่อผู้ติดต่อยามฉุกเฉิน'
                    ])
                    ->label('ชื่อ-นามสกุล ผู้ติดต่อ')
                    ->placeholder('กรอกชื่อ-นามสกุล ผู้ที่ติดต่อ'),
                TextInput::make('emergency_relation')
                    ->label('ความสัมพันธ์')
                    ->placeholder('ระบุความสัมพันธ์กับคุณเช่น "เป็นเพื่อน"'),
                TextInput::make('emergency_tel')
                    ->required()
                    ->validationMessages([
                        'required' => 'คุณยังไม่ได้กรอกเบอร์โทรศัพท์ของผู้ติดต่อ'
                    ])
                    ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                    ->mask('999-999-9999')
                    ->label('เบอร์โทรติดต่อ')
                    ->tel()
                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                Textarea::make('emergency_address')
                    ->label('ที่อยู่ปัจจุบัน')
                    ->placeholder('กรอกที่อยู่ของผู้ติดต่อ')
                    ->columnSpan(3)
                    ->autosize()
                    ->trim(),
                Select::make('province_id')
                    ->options(Provinces::pluck('name_th', 'id'))
                    ->live()
                    ->preload()
                    ->hiddenlabel()
                    ->placeholder('จังหวัด')
                    ->searchable()
                    ->afterStateUpdated(function ($state, $set) {

                        if ($state == null) {
                            $set('province_id', null);
                            $set('district_id', null);
                            $set('subdistrict_id', null);
                            $set('zipcode', null);
                        }
                    }),
                Select::make('district_id')
                    ->options(function (Get $get) {
                        $data = Districts::where('province_id', $get('province_id'))
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
                        $data = Subdistricts::where('district_id', $get('district_id'))
                            ->pluck('name_th', 'id');
                        return $data;
                    })
                    ->hiddenlabel()
                    ->preload()
                    ->placeholder('ตำบล')
                    ->live()
                    ->searchable()
                    ->afterStateUpdated(function ($state, $set) {
                        //รับค่าปัจจุบันในฟิลด์นี้หลังที่ Input ข้อมูลแล้ว
                        $zipcode = Subdistricts::where('id', $state)->pluck('zipcode'); //ไปที่ Subdistrict โดยที่ id = ปัจจุบันที่เราเลือก
                        $set('zipcode', Str::slug($zipcode)); //เอาค่าที่ได้ซึ่งเป็นอาเรย์มาถอดให้เหลือค่าอย่างเดียวด้วย Str::slug()แล้วเอาค่าที่ได้มาใส่ และส่งค่าไปยัง ฟิลด์ที่เลือกในที่นี้คือ zipcode
                    }),
                TextInput::make('zipcode')
                    ->live()
                    ->hiddenlabel()
                    ->placeholder('รหัสไปรษณีย์')

            ]);
    }

    public function healthInfoComponent()
    {
        return
            Section::make('ข้อมูลสุขภาพ')
            ->relationship('userHasoneAdditionalInfo')
            ->icon(
                fn($state) => blank($state['medical_condition'])
                    ? 'heroicon-m-exclamation-triangle'
                    : 'heroicon-m-check-circle'
            )
            ->iconColor(fn($state) => blank($state['medical_condition'])
                ? 'warning'
                : 'success')
            ->description(
                fn($state) => blank($state['medical_condition'])
                    ? 'คุณยังไม่ได้กรอกข้อมูลของบิดา กรุณากรอกข้อมูลให้ครบถ้วนตามจริง'
                    : null
            )
            ->collapsed()
            ->columns(2)
            ->schema([
                Fieldset::make('layout_medical')
                    ->columns(1)
                    ->contained(false)
                    ->hiddenLabel()
                    ->schema([
                        Radio::make('medical_condition')
                            ->required()
                            ->live()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้เลือกตัวเลือกใดตัวเลือกหนึ่ง'
                            ])
                            ->label('คุณมีโรคประจำตัวหรือไม่?')
                            ->options([
                                true => 'มีโรคประจำตัว',
                                false => 'ไม่มีโรคประจำตัว',
                            ])
                            ->inline(),
                        TextInput::make('medical_condition_detail')
                            ->visible(fn($get) => $get('medical_condition') ? 1 : 0)
                            ->prefix('โรค')
                            ->placeholder('ระบุโรคประจำตัวของคุณ')
                            ->label('รายละเอียดโรคประจำตัว'),
                    ]),
                Fieldset::make('layout_sso')
                    ->columns(1)
                    ->contained(false)
                    ->hiddenLabel()
                    ->schema([
                        Radio::make('has_sso')
                            ->required()
                            ->live()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้เลือกตัวเลือกใดตัวเลือกหนึ่ง'
                            ])
                            ->label('คุณยังมีประกันสังคมหรือไม่?')
                            ->options([
                                true => 'ยังมีประกันสังคม',
                                false => 'ไม่มีประกันสังคม',
                            ])
                            ->inline(),
                        TextInput::make('sso_hospital')
                            ->prefix('โรงพยาบาล')
                            ->visible(fn($get) => $get('has_sso') ? 1 : 0)
                            ->placeholder('ระบุโรคพยาบาลที่มีสิทธ์ประกันสังคม')
                            ->label('โรงพยาบาลที่เลือก'),
                    ])

            ]);
    }

    public function additionalComponent()
    {
        return
            Section::make('คำถามเพิ่มเติม')
            ->relationship('userHasoneAdditionalInfo')
            ->icon(
                fn($state) => blank($state['worked_company_before'])
                    ? 'heroicon-m-exclamation-triangle'
                    : 'heroicon-m-check-circle'
            )
            ->iconColor(
                fn($state) => blank($state['worked_company_before'])
                    ? 'warning'
                    : 'success'
            )
            ->description(
                fn($state) => blank($state['worked_company_before'])
                    ? 'คุณยังไม่ได้ตอบคำถามเพิ่มเติม กรุณากรอกข้อมูลให้ครบถ้วนตามจริง'
                    : null
            )
            ->collapsed()
            ->columns(2)
            ->schema([
                Fieldset::make('layout_worked')
                    ->columns(1)
                    ->contained(false)
                    ->hiddenLabel()
                    ->schema([
                        Radio::make('worked_company_before')
                            ->required()
                            ->live()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้เลือกตัวเลือกใดตัวเลือกหนึ่ง'
                            ])
                            ->label('คุณเคยทำงานกับบริษัทนี้หรือบริษัทในเครือมาก่อนหรือไม่?')
                            ->options([
                                true => 'เคย',
                                false => 'ไม่เคย',
                            ])
                            ->inline(),
                        TextInput::make('worked_company_supervisor')
                            ->visible(fn($get) => $get('worked_company_before') ? 1 : 0)
                            ->placeholder('ระบุชื่อของหัวหน้างานที่เคยทำงานด้วย')
                            ->label('ชื่อของหัวหน้า'),
                        Textarea::make('worked_company_detail')
                            ->visible(fn($get) => $get('worked_company_before') ? 1 : 0)
                            ->placeholder('กรอกรายละเอียดเพิ่มเติมเกี่ยวกับงานที่เคยทำ')
                            ->label('รายละเอียดเพิ่มเติม')
                            ->autosize()
                            ->trim(),

                    ]),
                Fieldset::make('layout_know')
                    ->columns(1)
                    ->contained(false)
                    ->hiddenLabel()
                    ->schema([
                        Radio::make('know_someone')
                            ->required()
                            ->live()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้เลือกตัวเลือกใดตัวเลือกหนึ่ง'
                            ])
                            ->label('คุณรู้จักพนักงานในบริษัทนี้หรือไม่?')
                            ->options([
                                true => 'รู้จัก',
                                false => 'ไม่รู้จัก',
                            ])
                            ->inline(),
                        TextInput::make('know_someone_name')
                            ->visible(fn($get) => $get('know_someone') ? 1 : 0)
                            ->placeholder('ระบุชื่อของพนักงานที่คุณรู้จักในบริษัทนี้')
                            ->label('ชื่อพนักงานที่รู้จัก'),
                        TextInput::make('know_someone_relation')
                            ->visible(fn($get) => $get('know_someone') ? 1 : 0)
                            ->placeholder('ระบุความสัมพันธ์เช่น เป็นเพื่อน')
                            ->label('ความสัมพันธ์'),


                    ]),
                TextInput::make('how_to_know_job')
                    ->columnSpan(2)
                    ->label('คุณรู้จักงานนี้ได้อย่างไร')
                    ->placeholder('ระบุแหล่งที่มาของการรับสมัครงานในตำแหน่งนี้เช่น Facebook'),
                Textarea::make('additional_info')
                    ->columnSpan(2)
                    ->placeholder('ข้อมูลเพิ่มเติมใดๆ จากผู้สมัครที่อาจเป็นประโยชน์ในกระบวนการคัดเลือกสำหรับบริษัท')
                    ->label('รายละเอียดเพิ่มเติม')
                    ->autosize()
                    ->trim(),
            ]);
    }
}
