<?php

namespace App\Filament\Pages;

use Dom\Text;
use Carbon\Carbon;
use App\Models\Districts;
use App\Models\Provinces;
use Detection\MobileDetect;
use Illuminate\Support\Str;
use App\Models\Subdistricts;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use function Termwind\style;
use App\Jobs\ProcessEmpDocJob;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use App\Events\ProcessEmpDocEvent;
use App\Filament\Components\UserFormComponent;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use App\Jobs\ProcessNoJsonEmpDocJob;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

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
    public bool $isAndroidOS;

    public function updateStateInFile($value)
    {
        $this->isSubmitDisabledFromFile = $value; // Disable if blank
    }

    public function updateStateInConfirm($value)
    {   //dump(!$value);
        $this->isSubmitDisabledFromConfirm = !$value;
    }

    public function getDocEmp($record, $action)
    {
        return $record->userHasmanyDocEmp()->where('file_name', $action->getName());
    }


    /************************************** */
    public function getActions(): array
    {
        $detect = new MobileDetect();
        $this->isMobile = $detect->isMobile();
        $this->isAndroidOS = $detect->isAndroidOS();
        return [
            ActionGroup::make([
                $this->imageProfile(),
                $this->idcardAction(),
                $this->resumeAction(),
                $this->transcriptAction(),
                $this->militaryAction(),
                $this->maritalAction(),
                $this->certificateAction(),
                $this->anotherDocAction(),
            ])->label('อับโหลดเอกสาร')
                ->icon('heroicon-m-document-arrow-up')
                ->color('primary')
                ->button(),
            Action::make('info')
                ->record(auth()->user())
                ->mountUsing(function (Schema $form) {
                    $form->fill(auth()->user()->attributesToArray());
                })
                ->icon('heroicon-m-user')
                ->label('กรอกข้อมูลเพิ่มเติม')
                ->tooltip('ท่านจำเป็นต้องกรอกข้อมูลบางอย่างที่ไม่มีในเอกสารที่ท่านอับโหลด')
                ->modalSubmitActionLabel('อับเดตข้อมูล')
                ->modalWidth(Width::FiveExtraLarge)
                ->closeModalByClickingAway(false)
                ->schema([
                    Tabs::make('Tabs')
                        ->persistTab()
                        ->tabs([
                            Tab::make('ข้อมูลครอบครัว')
                                ->extraAttributes(
                                    fn() => ($this->isMobile)
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
                                    fn() => ($this->isMobile)
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
                                    fn() => ($this->isMobile)
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
                                    fn() => ($this->isMobile)
                                        ? ['style' => 'padding-right: 12px; padding-left: 12px;']
                                        : []
                                )
                                ->schema(
                                    function () {
                                        return [(new UserFormComponent())->additionalComponent()];
                                    }
                                ),

                        ]),

                ])
                ->action(function ($action) {
                    $this->dispatch('openActionModal', id: $action->getName());
                    Notification::make()
                        ->title('บันทึกข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                }),
            Action::make('pdf')
                ->record(auth()->user())
                ->label('ดาวน์โหลดใบสมัคร')
                ->icon('heroicon-m-document-arrow-down')
                ->color('info')
                ->url(fn() =>
                blank($this->checkDocDownloaded()['upload']) &&
                    blank($this->checkDocDownloaded()['input'])
                    ? '/pdf'
                    : null)
                ->action(function ($record) {
                    $missing = $this->checkDocDownloaded();
                    $parts = [];

                    $hasUpload = !blank($missing['upload']);
                    $hasInput  = !blank($missing['input']);

                    if ($hasUpload) {
                        $parts[] = 'คุณยังไม่ได้อัปโหลดเอกสาร: <br>"' . implode(', ', $missing['upload']) . '"';
                    }

                    if ($hasInput) {
                        $parts[] = 'คุณยังไม่ได้กรอกข้อมูล: <br>"' . implode(', ', $missing['input']) . '"';
                    }

                    // ประโยคปิดท้าย
                    if ($hasUpload && $hasInput) {
                        $ending = 'กรุณาอัปโหลดเอกสาร และ กรอกข้อมูลดังกล่าว<br>ก่อนดาวน์โหลดใบสมัคร';
                        $msg = implode('<br><br>', $parts) . '<br><br>' . $ending;
                        event(new ProcessEmpDocEvent($msg, $record, 'popup', null, false));
                    }
                    if ($hasUpload) {
                        $ending = 'กรุณาอัปโหลดเอกสารก่อนดาวน์โหลดใบสมัคร';
                        $msg = implode('<br><br>', $parts) . '<br><br>' . $ending;
                        event(new ProcessEmpDocEvent($msg, $record, 'popup', null, false));
                    }
                    if ($hasInput) {
                        $ending = 'กรุณากรอกข้อมูลดังกล่าวก่อนดาวน์โหลดใบสมัคร';
                        $msg = implode('<br><br>', $parts) . '<br><br>' . $ending;
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
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action, $record) {
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {
                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
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

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();

                // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                return [
                    $action->getName() => $doc ? $doc->path : null,
                    'confirm' => $doc ? $doc->confirm : false,
                ];
            })

            ->action(function (array $data, $action) {
                $user = auth()->user();
                if (!blank($data[$action->getName()])) {
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("ลบ \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการลบ \"" . $action->getLabel() . "\" ใช่ไหม")
                            ->modalSubmitActionLabel('ยืนยันการลบ')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                if (!blank($doc)) {
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
            ->modalSubmitActionLabel(
                fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->extraModalWindowAttributes(
                fn() => $this->isMobile
                    ? ['style' => 'padding: 0px 5px']
                    : []
            )
            ->schema(function ($record, $action) {
                return [
                    ...(new UserFormComponent())->idcardComponent($record, $action->getName()),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->openable()
                        ->previewable(function ($state) {
                            $name = basename($state);
                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                            return $this->isAndroidOS && $extension === 'pdf' ? 0 : 1;
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {
                            $record->userHasoneIdcard()->delete();
                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
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

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();

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
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasoneIdcard()->delete();
                                $record->userHasoneFather()->delete();
                                $record->userHasoneMother()->delete();
                                $record->userHasmanySibling()->delete();
                                if (!blank($doc)) {
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
                fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($record, $action) {
                return [
                    (new UserFormComponent())->resumeComponent($record, $action->getName()),
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
                            return $this->isAndroidOS && $extension === 'pdf' ? 0 : 1;
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
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

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),
                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();
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
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                if (!blank($doc)) {
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
                $this->getDocEmp($record, $action)->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($record, $action) {
                return [
                    (new UserFormComponent())->transcriptComponent($record, $action->getName()),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->appendFiles()
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($state, $record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();
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
                            if (!blank($doc_transcript)) {
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

                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();

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
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasmanyTranscript()->delete();
                                if (!blank($doc)) {
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
            ->hidden(
                fn($record) =>
                in_array(trim(strtolower($record->userHasoneIdcard?->prefix_name_en), "."), ['miss', 'mrs'])
                    ? 1 : 0
            )
            ->closeModalByClickingAway(false)
            // ->modalSubmitAction(function ($action) {
            //     $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
            // })

            ->modalSubmitActionLabel(
                fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action, $record) {
                return [
                    (new UserFormComponent())->militaryComponent($record, $action->getName()),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->appendFiles()
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {
                            $record->userHasoneMilitary()->delete();
                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
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

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();

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
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasoneMilitary()->delete();
                                if (!blank($doc)) {
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

    public function maritalAction(): Action
    {
        return
            Action::make('marital')
            ->label('สถานะการสมรส')
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
                fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action, $record) {
                return [
                    (new UserFormComponent())->maritalComponent(),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->appendFiles()
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($record) use ($action) {
                            $record->userHasoneMarital()->delete();
                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
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

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();

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
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasoneMarital()->delete();
                                if (!blank($doc)) {
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

    public function certificateAction(): Action
    {
        return
            Action::make('certificate')
            ->label('ใบประกาศนียบัตร')
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
                fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->extraModalWindowAttributes(
                fn() => $this->isMobile
                    ? ['style' => 'padding: 0px 5px']
                    : []
            )
            ->schema(function ($action, $record) {
                return [
                    (new UserFormComponent())->certificateComponent($record, $action->getName()),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->appendFiles()
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($state, $record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();
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
                            if (!blank($doc_transcript)) {
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

                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();

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
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasmanyTranscript()->delete();
                                if (!blank($doc)) {
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

    public function anotherDocAction(): Action
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
                fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                    ? 'แก้ไขรายละเอียดข้อมูล'
                    : 'อับโหลดเอกสาร'
            )
            ->button()
            ->icon(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'heroicon-m-check-circle'
                : 'heroicon-m-exclamation-triangle')
            ->color(fn($action, $record) => $this->getDocEmp($record, $action)->exists()
                ? 'success'
                : 'warning')
            ->schema(function ($action, $record) {
                return [
                    (new UserFormComponent())->AnotherDocComponent(),
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
                            $this->updateStateInFile(blank($state));
                        })
                        ->afterStateHydrated(function () {
                            $this->updateStateInFile(true);
                        })
                        ->deleteUploadedFileUsing(function ($state, $record) use ($action) {
                            $doc = $this->getDocEmp($record, $action)->first();
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
                            if (!blank($doc_another)) {
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

                        ->afterStateUpdated(function ($state) {
                            $this->updateStateInConfirm($state);
                        }),

                ];
            })
            ->fillForm(function ($action, $record): array {
                $doc = $this->getDocEmp($record, $action)->first();

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
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
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
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("เคลียร์ข้อมูล" . $action->getLabel() . "ทั้งหมด")
                            ->requiresConfirmation()
                            ->modalHeading("เคลียร์ข้อมูลและเอกสาร \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการเคลียร์ข้อมูล \"" . $action->getLabel() . "\" รวมถึงไฟล์ด้วยใช่หรือไม่")
                            ->modalSubmitActionLabel('ยืนยันการเคลียร์ข้อมูล')
                            ->action(function ($record, $action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasmanyAnotherDoc()->delete();
                                if (!blank($doc)) {
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
        $errorUplaod = [ //สำหรับเอกสารอับโหลด
            'resume'        => $user->userHasoneResume()->exists(),
            'บัตรประชาชน'   => $user->userHasoneIdcard()->exists(),
            'วุฒิการศึกษา'   => $user->userHasmanyTranscript()->exists(),
        ];

        $additional = $user->userHasoneAdditionalInfo;

        $errorInput = [ //สำหรับข้อมูลที่ต้องกรอกเอง
            'บิดา' => blank($user->userHasoneFather->name),
            'มารดา' => blank($user->userHasoneMother->name),
            'ผู้ติดต่อยามฉุกเฉิน' => blank($additional->emergency_name),
            'คำถามสุขภาพ' => blank($additional->medical_condition),
            'คำถามเพิ่มเติม' => blank($additional->know_someone),
        ];

        // ใส่ใบเกณฑ์ทหารเฉพาะกรณี "ไม่ใช่ผู้หญิง"
        if (!$isFemale) {
            $errorUplaod['ใบเกณฑ์ทหาร'] = $user->userHasoneMilitary()->exists();
        }

        // หาเฉพาะรายการที่ยังไม่มีไฟล์
        $missingUplaod = array_keys(array_filter($errorUplaod, fn($v) => $v === false));
        $missingInput = array_keys(array_filter($errorInput, fn($v) => $v === true));
        return [
            'upload' => $missingUplaod,
            'input' => $missingInput,
        ];
    }

    public function fieldsteLabel($state)
    {
        $text = "ข้อมูลคู่สมรส";
        $icon = "⚠️"; // หรือ SVG icon
        $warning = "<div style='color: #FFA500; font-weight: bold;'>{$icon} คุณยังไม่ได้กรอกข้อมูลคู่สมรส</div>";
        return empty($state['alive']) ? $text . $warning : $text;
    }
}
