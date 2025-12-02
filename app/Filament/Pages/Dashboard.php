<?php

namespace App\Filament\Pages;

use Closure;
use Carbon\Carbon;
use PSpell\Config;
use App\Models\User;
use App\Models\Districts;
use App\Models\Provinces;
use App\Models\AnotherDoc;
use Detection\MobileDetect;
use Illuminate\Support\Str;
use App\Models\Subdistricts;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Jobs\ProcessEmpDocJob;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use App\Events\ProcessEmpDocEvent;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessNoJsonEmpDocJob;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Redirect;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\View\Components\ModalComponent;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Dashboard extends BaseDashboard
{
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
    public $modal = [
        'is_open' => false,
        'action_id' => null
    ];

    protected $listeners = [
        'openActionModal' => 'openActionModal',
        'refreshActionModal' => 'refreshActionModal',
    ];

    public bool $isSubmitDisabledFromFile = true;
    public bool $isSubmitDisabledFromConfirm = true;
    public bool $isMobile;

    public function updateStateInFile($value)
    {
        $this->isSubmitDisabledFromFile = $value; // Disable if empty
    }

    public function updateStateInConfirm($value)
    {   //dump(!$value);
        $this->isSubmitDisabledFromConfirm = !$value;
    }


    /************************************** */
    public function getActions(): array
    {
        $detect = new MobileDetect();
        $this->isMobile = $detect->isMobile();
        return [
            ActionGroup::make([
                $this->imageProfile(),
                $this->idcardAction(),
                $this->resumeAction(),
                $this->transcriptAction(),
                $this->militaryAction(),
                $this->AnotherDocAction(),
            ])
                ->label('อับโหลดเอกสาร')
                //->extraAttributes([
                //'style' => 'font-size: 1.3rem;',
                //])
                ->icon('heroicon-m-document-arrow-up')
                //->size(Size::Large)
                ->color('primary')
                ->button(),
            Action::make('pdf')
                ->record(auth()->user())
                ->label('ดาวน์โหลดใบสมัคร')
                ->icon('heroicon-m-document-arrow-down')
                ->color('info')
                ->url(fn() => count($this->checkDocDownloaded()) === 0 ? '/pdf' : null)
                ->action(function ($record) {
                    $missing = $this->checkDocDownloaded();
                    if (count($missing) > 0) {
                        $msg = 'คุณยังไม่ได้อัปโหลดเอกสาร: <br>' .
                            '"' . implode(', ', $missing) . '"' .
                            '<br>กรุณาอัปโหลดเอกสารดังกล่าวก่อนดาวน์โหลดใบสมัคร';

                        event(new ProcessEmpDocEvent($msg, $record, 'popup', null, false));
                    }
                })
                ->openUrlInNewTab()
                ->button(),


        ];
    }

    public function imageProfile(): Action
    {
        return
            Action::make('image_profile')
            ->label('รูปโปรไฟล์')
            ->modalWidth(Width::FiveExtraLarge)
            ->record(auth()->user())
            ->closeModalByClickingAway(false)
            ->modalSubmitAction(function ($action) {
                $action->disabled(
                    fn(): bool => (
                        $this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm
                    )
                );
            })
            ->modalSubmitActionLabel('อับโหลดรูปโปรไฟล์')
            ->button()
            ->icon(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action) {
                return [
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->openable()
                        ->pdfPreviewHeight(400) // Customize preview height
                        ->pdfDisplayPage(1) // Set default page
                        ->pdfToolbar(true) // Enable toolbar
                        ->pdfZoomLevel(100) // Set zoom level
                        ->pdfFitType(PdfViewFit::FIT) // Set fit type
                        ->pdfNavPanes(true) // Enable navigation panes
                        ->label('เลือกไฟล์')
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->required()
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('2.8:3.5')
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();
                            $userEmail = $record->email;
                            return "{$userEmail}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                            $this->updateStateInFile(empty($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {
                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();

                            if (!empty($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
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
                        ->disabled(function ($record) use ($action) {

                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();
                            return !empty($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

                // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                return [
                    $action->getName() => $doc ? $doc->path : null,
                    'confirm' => $doc ? $doc->confirm : false,
                ];
            })

            ->action(function (array $data, $action) {
                $user = auth()->user();
                if (!empty($data[$action->getName()])) {
                    $user->userHasmanyDocEmp()->updateOrCreate(
                        ['file_name' => $action->getName()],
                        [
                            'user_id' => $user->id,
                            'file_name_th' => $action->getLabel(),
                            'path' => $data[$action->getName()],
                            'confirm' => $data['confirm'],
                        ]
                    );
                }
                $this->dispatch('openActionModal', id: $action->getName());
            })
            ->extraModalFooterActions(
                function ($action) {
                    return [
                        DeleteAction::make($action->getName())
                            ->hidden(function ($record) use ($action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                return empty($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("ลบ \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการลบ \"" . $action->getLabel() . "\" ใช่ไหม")
                            ->modalSubmitActionLabel('ยืนยันการลบ')
                            ->action(function ($record, $action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                if (!empty($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }

                                $this->dispatch('refreshActionModal', id: $action->getName());
                            })
                            ->successNotificationTitle('ลบรูปโปรไฟล์เรียบร้อยแล้ว')
                    ];
                }
            );
    }

    public function idcardAction(): Action
    {
        return
            Action::make('idcard')
            ->label('บัตรประชาชน')
            ->mountUsing(function (Schema $form) {
                $form->fill(auth()->user()->attributesToArray());
            })
            ->modalWidth(Width::FiveExtraLarge)
            ->record(auth()->user())
            ->closeModalByClickingAway(false)
            // ->modalSubmitAction(function ($action) {
            //     $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
            // })

            ->modalSubmitActionLabel(
                fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'success'
                : 'warning')
            ->extraModalWindowAttributes(
                fn() => $this->isMobile
                    ? ['style' => 'padding: 0px 5px']
                    : []
            )
            ->schema(function ($action) {
                return [
                    Section::make('ข้อมูลบัตรประชาชนทั่วไป')
                        ->hidden(function ($record) use ($action) {
                            $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                            return empty($doc) ? 1 : 0;
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
                        ->hidden(function ($record) use ($action) {
                            $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                            return empty($doc) ? 1 : 0;
                        })
                        ->columns(3)
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
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->openable()
                        ->previewable(function ($state) {
                            $name = basename($state);
                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                            return $this->isMobile && $extension === 'pdf' ? 0 : 1;
                        })
                        ->label('เลือกไฟล์')
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();
                            $userEmail = $record->email;
                            return "{$userEmail}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                            $this->updateStateInFile(empty($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {
                            $record->userHasOneIdcard()->delete();
                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();

                            if (!empty($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
                            $this->dispatch('refreshActionModal', id: $action->getName());
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
                        ->disabled(function ($record) use ($action) {

                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();
                            return !empty($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

                // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                return [
                    $action->getName() => $doc ? $doc->path : null,
                    'confirm' => $doc ? $doc->confirm : false,
                ];
            })

            ->action(function (array $data, $action) {

                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('Saved successfully')
                        ->color('success')
                        ->send();
                    $this->dispatch('openActionModal', id: $action->getName());
                } else {
                    $user->userHasmanyDocEmp()->updateOrCreate(
                        ['file_name' => $action->getName()],
                        [
                            'user_id' => $user->id,
                            'file_name_th' => $action->getLabel(),
                            'path' => $data[$action->getName()],
                            'confirm' => $data['confirm'],
                        ]
                    );

                    ProcessEmpDocJob::dispatch(
                        $data[$action->getName()],
                        $user,
                        $action->getName(),
                        $action->getLabel()
                    );
                }
            })
            ->extraModalFooterActions(
                function ($action) {
                    return [
                        DeleteAction::make($action->getName())
                            ->hidden(function ($record) use ($action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                return empty($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                $record->userHasOneIdcard()->delete();
                                if (!empty($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $this->dispatch('refreshActionModal', id: $action->getName());
                            })
                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว')

                    ];
                }
            );
    }

    public function resumeAction(): Action
    {
        return
            Action::make('resume')
            ->label('เรซูเม่')
            ->mountUsing(function (Schema $form) {
                $form->fill(auth()->user()->attributesToArray());
            })
            ->modalWidth(Width::FiveExtraLarge)
            ->record(auth()->user())
            ->closeModalByClickingAway(false)
            // ->modalSubmitAction(function ($action) {
            //     $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
            // })
            ->modalSubmitActionLabel(
                fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action) {
                return [
                    Tabs::make('Tabs')
                        ->persistTab()
                        ->hidden(function ($record) use ($action) {
                            $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                            return empty($doc) ? 1 : 0;
                        })
                        ->tabs([
                            Tab::make('ข้อมูลเรซูเม่ทั่วไป')
                                ->extraAttributes(
                                    fn() => ($this->isMobile)
                                        ? ['style' => 'padding: 24px 15px']
                                        : []
                                )
                                ->schema([
                                    Section::make('คลิกเพื่อดูข้อมูลทั่วไป')
                                        ->contained(false)
                                        ->hiddenLabel()
                                        ->description('แสดงรายละเอียดข้อมูลทั่วไปจาก "เรซูเม่" โปรดตรวจสอบข้อมูลให้ถูกต้อง')
                                        ->schema([
                                            Fieldset::make('gernaral_info')
                                                ->label('ข้อมูลเรซูเม่ทั่วไป')
                                                ->relationship('userHasoneResume')
                                                ->extraAttributes(
                                                    fn() => $this->isMobile
                                                        ? ['style' => 'padding: 24px 10px']
                                                        : []
                                                )
                                                ->columns(4)
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
                                                    Select::make('marital_status')
                                                        ->label('สถานภาพสมรส')
                                                        ->placeholder('สถานภาพสมรส')
                                                        ->options(config('iconf.marital_status')),
                                                    TextInput::make('height')
                                                        ->label('ส่วนสูง')
                                                        ->placeholder('ระบุส่วนสูง cm')
                                                        ->postfix('cm'),
                                                    TextInput::make('weight')
                                                        ->label('น้ำหนัก')
                                                        ->placeholder('ระบุน้ำหนัก kg')
                                                        ->postfix('kg'),
                                                ]),
                                            Fieldset::make('other_contact')
                                                ->label('ข้อมูลผู้ที่ติดต่อได้')
                                                ->extraAttributes(
                                                    fn() => $this->isMobile
                                                        ? ['style' => 'padding: 24px 10px']
                                                        : []
                                                )
                                                ->schema([
                                                    Repeater::make('contact')
                                                        ->relationship('userHasManyResumeToOtherContact')
                                                        ->hiddenLabel()
                                                        ->columns(3)
                                                        ->columnSpanFull()
                                                        ->addActionLabel('เพิ่ม "ผู้ติดต่อได้"')
                                                        ->itemNumbers()
                                                        ->schema([
                                                            TextInput::make('name')
                                                                ->label('ชื่อ-นามสกุล')
                                                                ->placeholder('ระบุชื่อผู้ติดต่อ'),
                                                            TextInput::make('email')
                                                                ->label('อีเมล')
                                                                ->email()
                                                                ->placeholder('ระบุอีเมลของผู้ติดต่อ'),
                                                            TextInput::make('tel')
                                                                ->columnSpan(1)
                                                                ->placeholder('เบอร์โทรศัพท์ (กรอกเฉพาะตัวเลข)')
                                                                ->mask('999-999-9999')
                                                                ->label('เบอร์โทรศัพท์')
                                                                ->tel()
                                                                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                                                        ])
                                                ]),
                                        ])->collapsed()
                                ]),
                            Tab::make('ที่อยู่ปัจจุบัน')
                                ->extraAttributes(
                                    fn() => ($this->isMobile)
                                        ? ['style' => 'padding: 24px 15px']
                                        : []
                                )
                                ->schema([
                                    Section::make('คลิกดูที่อยู่ปัจจุบัน')
                                        ->description('แสดงที่อยู่ปัจจุบันที่ติดต่อได้ เพื่อการส่งเอกสารที่จำเป็นไปให้ท่านได้ถูกต้อง')
                                        ->contained(false)
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
                                ]),
                            Tab::make('ตำแหน่งงาน')
                                ->extraAttributes(
                                    fn() => ($this->isMobile)
                                        ? ['style' => 'padding: 24px 15px']
                                        : []
                                )
                                ->schema([
                                    Section::make('คลิกดูตำแหน่งงาน')
                                        ->contained(false)
                                        ->relationship('userHasOneResumeToJobPreference')
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
                                                                    if (! empty($state)) {
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
                                                        ->options(Provinces::pluck('name_th', 'id'))
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
                                    Section::make('คลิกดูประสบการณ์ทำงาน')
                                        ->description("แสดงข้อมูลประสบการณ์ทำงานของท่าน สามารถกรอกข้อมูลเพิ่มเติมได้")
                                        ->contained(false)
                                        ->schema([
                                            Repeater::make('experiences')
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
                                                        ->columnSpanFull(),
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
                                    Section::make('คลิกดูความสามาถทางภาษา')
                                        ->contained(false)
                                        ->description("แสดงข้อมูลทักษะด้านภาษาของท่าน สามารถกรอกข้อมูลเพิ่มเติมได้")
                                        ->schema([
                                            Repeater::make('langskill')
                                                ->columns(4)
                                                ->hiddenLabel()
                                                ->addActionLabel('เพิ่ม "ความสามารถทางภาษา"')
                                                ->relationship('userHasManyResumeToLangSkill')
                                                ->schema([
                                                    TextInput::make('language')
                                                        ->label('ภาษา')
                                                        ->placeholder('กรอกความสามารถทางภาษา')
                                                        ->afterStateHydrated(function ($component, $state) {
                                                            if (! empty($state)) {
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
                                    Section::make('คลิกดูความสามาถด้านอื่นๆ')
                                        ->contained(false)
                                        ->description("แสดงข้อมูลทักษะด้านอื่นๆ ของท่าน สามารถกรอกข้อมูลเพิ่มเติมได้")
                                        ->schema([
                                            Repeater::make('skills')
                                                ->columns(2)
                                                ->hiddenLabel()
                                                ->addActionLabel('เพิ่ม "ความสามารถอื่นๆ"')
                                                ->relationship('userHasManyResumeToSkill')
                                                ->schema([
                                                    TextInput::make('skill_name')
                                                        ->label('ภาษา')
                                                        ->placeholder('กรอกความสามารถทางภาษา')
                                                        ->afterStateHydrated(function ($component, $state) {
                                                            if (! empty($state)) {
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

                        ]),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->openable()
                        ->label('เลือกไฟล์')
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->live()
                        ->directory('emp_files')
                        ->required()
                        ->previewable(function ($state) {
                            $name = basename($state);
                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                            return $this->isMobile && $extension === 'pdf' ? 0 : 1;
                        })
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();
                            $userEmail = $record->email;
                            return "{$userEmail}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                            $this->updateStateInFile(empty($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {

                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();

                            if (!empty($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
                            $record->userHasoneResume()->delete();
                            $this->dispatch('refreshActionModal', id: $action->getName());
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
                        ->disabled(function ($record) use ($action) {

                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();
                            return !empty($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),
                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                return [
                    $action->getName() => $doc ? $doc->path : null,
                    'confirm' => $doc ? $doc->confirm : false,
                ];
            })

            ->action(function (array $data, $action) {

                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('Saved successfully')
                        ->color('success')
                        ->send();
                    $this->dispatch('openActionModal', id: $action->getName());
                } else {
                    $user->userHasmanyDocEmp()->updateOrCreate(
                        ['file_name' => $action->getName()],
                        [
                            'user_id' => $user->id,
                            'file_name_th' => $action->getLabel(),
                            'path' => $data[$action->getName()],
                            'confirm' => $data['confirm'],
                        ]
                    );

                    ProcessEmpDocJob::dispatch(
                        $data[$action->getName()],
                        $user,
                        $action->getName(),
                        $action->getLabel()
                    );
                }
            })
            ->extraModalFooterActions(
                function ($action) {
                    return [
                        DeleteAction::make($action->getName())
                            ->hidden(function ($record) use ($action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                return empty($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                if (!empty($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $record->userHasoneResume()->delete();
                                $this->dispatch('refreshActionModal', id: $action->getName());
                            })
                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว')
                    ];
                }
            );
    }

    public function transcriptAction(): Action
    {
        return
            Action::make('transcript')
            ->label('ใบแสดงผลการศึกษา')
            ->mountUsing(function (Schema $form) {
                $form->fill(auth()->user()->attributesToArray());
            })
            ->modalWidth(Width::FiveExtraLarge)
            ->record(auth()->user())
            ->closeModalByClickingAway(false)
            // ->modalSubmitAction(function ($action) {
            //     $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
            // })

            ->modalSubmitActionLabel(
                fn($action, $record) =>
                $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action) {
                return [
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
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->appendFiles()
                        ->openable()
                        ->previewable(function ($state) {
                            return $this->isMobile ? 0 : 1;
                        })
                        ->panelLayout(function () {
                            return $this->isMobile ? null : 'grid';
                        })
                        ->label('เลือกไฟล์')
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->multiple()
                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();
                            $userEmail = $record->email;
                            return "{$userEmail}/{$action->getName()}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                            $this->updateStateInFile(empty($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($state, $record) use ($action) {

                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();
                            $path = $doc->path;

                            $fileDelete = array_values(array_diff($path, $state));
                            if (count($path) > 1) {
                                //dump($fileDelete);
                                Storage::disk('public')->delete($fileDelete[0]);
                                //dump($path);
                                $pathSuccess = array_values(array_diff($path, $fileDelete));
                                //dump($pathSuccess);
                                $record->userHasmanyDocEmp()->updateOrCreate(
                                    ['file_name' => $action->getName()],
                                    ['path' => $pathSuccess]
                                );
                            } else {
                                Storage::disk('public')->delete($path);
                                $doc->delete();
                            }
                            $doc_transcript = $record->userHasmanyTranscript()
                                ->where('file_path', $fileDelete[0])
                                ->first();
                            if (!empty($doc_transcript)) {
                                $doc_transcript->delete();
                            }
                            $this->dispatch('refreshActionModal', id: $action->getName());
                            $this->updateStateInConfirm(true);
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
                        // ->disabled(function ($record) use ($action) {
                        //     $user = auth()->user();
                        //     $doc = $record->userHasmanyDocEmp()
                        //         ->where('file_name', $action->getName())
                        //         ->first();
                        //     return !empty($doc) ? 1 : 0;
                        // })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

                // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                return [
                    $action->getName() => $doc ? $doc->path : null,
                    'confirm' => $doc ? $doc->confirm : false,
                ];
            })

            ->action(function (array $data, $action) {
                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                $fileForSend = array_values(array_diff($data[$action->getName()], $doc->path ?? []));

                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('Saved successfully')
                        ->color('success')
                        ->send();
                    $this->dispatch('openActionModal', id: $action->getName());
                } else {
                    $user->userHasmanyDocEmp()->updateOrCreate(
                        ['file_name' => $action->getName()],
                        [
                            'user_id' => $user->id,
                            'file_name_th' => $action->getLabel(),
                            'path' => $data[$action->getName()],
                            'confirm' => $data['confirm'],
                        ]
                    );

                    ProcessNoJsonEmpDocJob::dispatch(
                        $fileForSend,
                        $user,
                        $action->getName(),
                        $action->getLabel()
                    );
                }
            })
            ->extraModalFooterActions(
                function ($action) {
                    return [
                        DeleteAction::make($action->getName())
                            ->hidden(function ($record) use ($action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                return empty($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                $record->userHasmanyTranscript()->delete();
                                if (!empty($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $this->dispatch('refreshActionModal', id: $action->getName());
                            })
                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว')

                    ];
                }
            );
    }

    public function militaryAction(): Action
    {
        return
            Action::make('military')
            ->label('ใบเกณฑ์หทาร')
            ->mountUsing(function (Schema $form) {
                $form->fill(auth()->user()->attributesToArray());
            })
            ->modalWidth(Width::FiveExtraLarge)
            ->record(auth()->user())
            ->hidden(fn($record) =>
            in_array(trim(strtolower($record->userHasoneIdcard?->prefix_name_en), "."), ['miss', 'mrs'])
                ? 1
                : 0)
            ->closeModalByClickingAway(false)
            // ->modalSubmitAction(function ($action) {
            //     $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
            // })

            ->modalSubmitActionLabel(
                fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action) {
                return [
                    Section::make('ข้อมูลใบเกณฑ์หทาร')
                        ->hidden(function ($record) use ($action) {
                            $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                            return empty($doc) ? 1 : 0;
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

                        ])->collapsed(),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->appendFiles()
                        ->openable()
                        ->previewable(function ($state) {
                            return $this->isMobile ? 0 : 1;
                        })
                        ->label('เลือกไฟล์')
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')

                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();
                            $userEmail = $record->email;
                            return "{$userEmail}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                            $this->updateStateInFile(empty($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {
                            $record->userHasoneMilitary()->delete();
                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();

                            if (!empty($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
                            $this->dispatch('refreshActionModal', id: $action->getName());
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
                        ->disabled(function ($record) use ($action) {

                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();
                            return !empty($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

                // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                return [
                    $action->getName() => $doc ? $doc->path : null,
                    'confirm' => $doc ? $doc->confirm : false,
                ];
            })

            ->action(function (array $data, $action) {

                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('Saved successfully')
                        ->color('success')
                        ->send();
                    $this->dispatch('openActionModal', id: $action->getName());
                } else {
                    $user->userHasmanyDocEmp()->updateOrCreate(
                        ['file_name' => $action->getName()],
                        [
                            'user_id' => $user->id,
                            'file_name_th' => $action->getLabel(),
                            'path' => $data[$action->getName()],
                            'confirm' => $data['confirm'],
                        ]
                    );

                    ProcessEmpDocJob::dispatch(
                        $data[$action->getName()],
                        $user,
                        $action->getName(),
                        $action->getLabel()
                    );
                }
            })
            ->extraModalFooterActions(
                function ($action) {
                    return [
                        DeleteAction::make($action->getName())
                            ->hidden(function ($record) use ($action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                return empty($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                $record->userHasoneMilitary()->delete();
                                if (!empty($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $this->dispatch('refreshActionModal', id: $action->getName());
                            })
                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว')

                    ];
                }
            );
    }

    public function AnotherDocAction(): Action
    {
        return
            Action::make('another')
            ->label('เอกสารเพิ่มเติม')
            ->mountUsing(function (Schema $form) {
                $form->fill(auth()->user()->attributesToArray());
            })
            ->modalWidth(Width::FiveExtraLarge)
            ->record(auth()->user())
            ->closeModalByClickingAway(false)
            // ->modalSubmitAction(function ($action) {
            //     $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
            // })
            ->modalSubmitActionLabel(
                fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $record->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action) {
                return [
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
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->appendFiles()
                        ->openable()
                        ->label('เลือกไฟล์')
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->multiple()
                        ->reorderable()
                        ->panelLayout(function () {
                            return $this->isMobile ? null : 'grid';
                        })
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $extension = $file->getClientOriginalExtension();
                            $name = $file->getClientOriginalName();
                            $userEmail = $record->email;
                            return "{$userEmail}/{$action->getName()}/{$name}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                            $this->updateStateInFile(empty($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($state, $record) use ($action) {
                            $doc = $record->userHasmanyDocEmp()
                                ->where('file_name', $action->getName())
                                ->first();
                            $path = $doc->path;

                            $fileDelete = array_values(array_diff($path, $state));
                            if (count($path) > 1) {
                                //dump($fileDelete);
                                Storage::disk('public')->delete($fileDelete[0]);
                                //dump($path);
                                $pathSuccess = array_values(array_diff($path, $fileDelete));
                                //dump($pathSuccess);
                                $record->userHasmanyDocEmp()->updateOrCreate(
                                    ['file_name' => $action->getName()],
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
                            $this->dispatch('refreshActionModal', id: $action->getName());
                            $this->updateStateInConfirm(true);
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
                        // ->disabled(function ($record) use ($action) {
                        //     $user = auth()->user();
                        //     $doc = $record->userHasmanyDocEmp()
                        //         ->where('file_name', $action->getName())
                        //         ->first();
                        //     return !empty($doc) ? 1 : 0;
                        // })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

                // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                return [
                    $action->getName() => $doc ? $doc->path : null,
                    'confirm' => $doc ? $doc->confirm : false,
                ];
            })
            ->action(function (array $data, $action) {
                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                $fileForSend = array_values(array_diff($data[$action->getName()], $doc->path ?? []));

                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('Saved successfully')
                        ->color('success')
                        ->send();
                    $this->dispatch('openActionModal', id: $action->getName());
                } else {
                    $user->userHasmanyDocEmp()->updateOrCreate(
                        ['file_name' => $action->getName()],
                        [
                            'user_id' => $user->id,
                            'file_name_th' => $action->getLabel(),
                            'path' => $data[$action->getName()],
                            'confirm' => $data['confirm'],
                        ]
                    );

                    ProcessNoJsonEmpDocJob::dispatch(
                        $fileForSend,
                        $user,
                        $action->getName(),
                        $action->getLabel()
                    );
                }
            })
            ->extraModalFooterActions(
                function ($action) {
                    return [
                        DeleteAction::make($action->getName())
                            ->hidden(function ($record) use ($action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                return empty($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $record->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                                $record->userHasmanyAnotherDoc()->delete();
                                if (!empty($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $this->dispatch('refreshActionModal', id: $action->getName());
                            })
                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว')

                    ];
                }
            );
    }


    /*****************เกี่ยวกับ Mount Action******************* */
    public function openActionModal($id = null)
    {
        $this->mountAction($id);
    }

    public function refreshActionModal($id = null)
    {
        $this->unmountAction();
        $this->mountAction($id);
    }

    /****************ฟังก์ชั่นพิเศษสำหรับเช็คว่าโหลดเอกสารหรือยัง***************** */
    public function checkDocDownloaded()
    {
        $user = auth()->user();

        // ดึง prefix จากบัตรประชาชน (ถ้ามี)
        $prefix = $user->userHasoneIdcard?->prefix_name_en;
        // ตรวจว่าเป็นผู้หญิงหรือไม่
        $isFemale = in_array(trim(strtolower($prefix), "."), ['miss', 'mrs']);
        $error = [
            'resume'        => $user->userHasoneResume()->exists(),
            'บัตรประชาชน'   => $user->userHasoneIdcard()->exists(),
            'วุฒิการศึกษา'   => $user->userHasmanyTranscript()->exists(),
        ];

        // ใส่ใบเกณฑ์ทหารเฉพาะกรณี "ไม่ใช่ผู้หญิง"
        if (!$isFemale) {
            $error['ใบเกณฑ์ทหาร'] = $user->userHasoneMilitary()->exists();
        }

        // หาเฉพาะรายการที่ยังไม่มีไฟล์
        $missing = array_keys(array_filter($error, fn($v) => $v === false));
        return $missing;
    }
}
