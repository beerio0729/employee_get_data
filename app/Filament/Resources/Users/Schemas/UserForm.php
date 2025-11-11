<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Districts;
use App\Models\Provinces;
use Illuminate\Support\Str;
use App\Models\Subdistricts;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

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
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('ข้อมูลทั่วไป')
                            ->schema([
                                Fieldset::make('employee')
                                    ->hiddenLabel()
                                    ->columns(3)
                                    ->contained(false)
                                    ->relationship('userHasoneEmployee')
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
                                    ]),
                                FileUpload::make('image')
                                    ->label('กรุณาอับโหลดรูปภาพ')
                                    ->disk('public')
                                    //->visibility('private')
                                    ->preserveFilenames()
                                    ->avatar()
                                    ->directory('img_storage')
                                    //->maxSize(3000)
                                    //->columnSpan(2)
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ]),
                            ]),
                        Tab::make('ที่อยู่')
                            //->columns(3)
                            ->schema([
                                Fieldset::make('emp_location')
                                    ->hiddenLabel()
                                    ->columns(3)
                                    ->contained(false)
                                    ->relationship('userHasonelocation')
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
                                    ])

                            ]),
                        Tab::make('ประวัติการศึกษา')
                            ->schema([
                                Repeater::make('educations')
                                    ->hiddenLabel()
                                    ->relationship('userHasmanyEducation')
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
                                    ])

                            ]),
                        Tab::make('ประวัติการทำงาน')
                            ->schema([
                                Repeater::make('experiences')
                                    ->hiddenLabel()
                                    ->relationship('userHasmanyWorkExperiences')
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
                                    ])

                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
