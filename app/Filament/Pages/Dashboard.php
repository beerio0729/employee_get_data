<?php

namespace App\Filament\Pages;


use Closure;
use Filament\Actions\Action;
use App\Jobs\ProcessEmpDocJob;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Filament\Schemas\Components\Component;
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
        'action_id' => null,
    ];

    protected $listeners = [
        'document-upload-error' => 'openFailedActionModal',
    ];

    public bool $isSubmitDisabledFromFile = true;
    public bool $isSubmitDisabledFromConfirm = true;


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
        return [
            ActionGroup::make([
                Action::make('image_profile')
                    ->label('รูปโปรไฟล์')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitAction(function ($action) {
                        $action->disabled(
                            fn(): bool => (
                                $this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm
                            )
                        );
                    })
                    ->modalSubmitActionLabel(
                        fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'success'
                        : 'warning')
                    ->schema(function ($action) {
                        return [
                            AdvancedFileUpload::make($action->getName())
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
                                ->validationMessages([
                                    'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                                ])
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($action) {
                                    $i = mt_rand(1000, 9000);
                                    $extension = $file->getClientOriginalExtension();
                                    $userEmail = auth()->user()->email;
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
                                ->deleteUploadedFileUsing(function () use ($action) {
                                    // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                    $user = auth()->user();
                                    $doc = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();

                                    if ($doc) {
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
                                ->disabled(function () use ($action) {
                                    $user = auth()->user();
                                    $doc_file = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();
                                    return !empty($doc_file) ? 1 : 0;
                                })
                                ->afterStateUpdated(function ($state) {
                                    $this->updateStateInConfirm($state);
                                }),

                        ];
                    })
                    ->fillForm(function ($action): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

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
                        return Redirect("/profile?tab=resume::data::tab");
                    }),
                Action::make('resume')
                    ->label('เรซูเม่')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitAction(function ($action) {
                        $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
                    })

                    ->modalSubmitActionLabel(
                        fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'success'
                        : 'warning')
                    ->schema(function ($action) {
                        return [
                            AdvancedFileUpload::make($action->getName())
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
                                ->validationMessages([
                                    'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                                ])
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($action) {
                                    $i = mt_rand(1000, 9000);
                                    $extension = $file->getClientOriginalExtension();
                                    $userEmail = auth()->user()->email;
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
                                ->deleteUploadedFileUsing(function () use ($action) {
                                    // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                    $user = auth()->user();
                                    $doc_file = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();

                                    if ($doc_file) {
                                        Storage::disk('public')->delete($doc_file->path);
                                        $doc_file->delete();
                                        // 3.1 HasOne Relations (Location, JobPreference)
                                        $user->userHasOneResumeToLocation()->delete(); // ต้องเรียกเมธอดที่สร้าง Relation
                                        $user->userHasOneResumeToJobPreference()->delete();

                                        // 3.2 HasMany Relations (Education, Work Experiences, etc.)
                                        $user->userHasManyResumeToEducation()->delete();
                                        $user->userHasManyResumeToWorkExperiences()->delete();
                                        $user->userHasManyResumeToLangSkill()->delete();
                                        $user->userHasManyResumeToSkill()->delete();
                                        $user->userHasManyResumeToCertificate()->delete();
                                        $user->userHasManyResumeToOtherContact()->delete();

                                        $user->userHasoneResume()->update([
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
                                ->disabled(function () use ($action) {
                                    $user = auth()->user();
                                    $doc_file = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();
                                    return !empty($doc_file) ? 1 : 0;
                                })
                                ->afterStateUpdated(function ($state) {
                                    $this->updateStateInConfirm($state);
                                }),
                        ];
                    })
                    ->fillForm(function ($action): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

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

                        ProcessEmpDocJob::dispatch($data, $user, $action->getLabel());
                    }),
                Action::make('idcard')
                    ->label('บัตรประชาชน')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitAction(function ($action) {
                        $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
                    })

                    ->modalSubmitActionLabel(
                        fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'success'
                        : 'warning')
                    ->schema(function ($action) {
                        return [
                            AdvancedFileUpload::make($action->getName())
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
                                ->validationMessages([
                                    'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                                ])
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($action) {
                                    $i = mt_rand(1000, 9000);
                                    $extension = $file->getClientOriginalExtension();
                                    $userEmail = auth()->user()->email;
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
                                ->deleteUploadedFileUsing(function () use ($action) {
                                    // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                    $user = auth()->user();
                                    $doc = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();

                                    if ($doc) {
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
                                ->disabled(function () use ($action) {
                                    $user = auth()->user();
                                    $doc_file = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();
                                    return !empty($doc_file) ? 1 : 0;
                                })
                                ->afterStateUpdated(function ($state) {
                                    $this->updateStateInConfirm($state);
                                }),

                        ];
                    })
                    ->fillForm(function ($action): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

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
                        ProcessEmpDocJob::dispatch($data, $user, $action->getLabel());
                    }),
                Action::make('transcript')
                    ->label('ใบแสดงผลการศึกษา')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitAction(function ($action) {
                        $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
                    })

                    ->modalSubmitActionLabel(
                        fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'success'
                        : 'warning')
                    ->schema(function ($action) {
                        return [
                            AdvancedFileUpload::make($action->getName())
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
                                ->multiple()
                                ->required()
                                ->validationMessages([
                                    'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                                ])
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($action) {
                                    $i = mt_rand(1000, 9000);
                                    $extension = $file->getClientOriginalExtension();
                                    $userEmail = auth()->user()->email;
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
                                ->deleteUploadedFileUsing(function () use ($action) {
                                    // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                    $user = auth()->user();
                                    $doc = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();

                                    if ($doc) {
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
                                ->disabled(function () use ($action) {
                                    $user = auth()->user();
                                    $doc_file = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();
                                    return !empty($doc_file) ? 1 : 0;
                                })
                                ->afterStateUpdated(function ($state) {
                                    $this->updateStateInConfirm($state);
                                }),

                        ];
                    })
                    ->fillForm(function ($action): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

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
                        ProcessEmpDocJob::dispatch($data, $user, $action->getLabel());
                    }),
                Action::make('bookbank')
                    ->label('สมุดบัญชีธนาคาร')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitAction(function ($action) {
                        $action->disabled(fn(): bool => ($this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm));
                    })

                    ->modalSubmitActionLabel(
                        fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn($action) => auth()->user()->userHasmanyDocEmp()->where('file_name', $action->getName())->exists()
                        ? 'success'
                        : 'warning')
                    ->schema(function ($action) {
                        return [
                            AdvancedFileUpload::make($action->getName())
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
                                ->validationMessages([
                                    'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                                ])
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($action) {
                                    $i = mt_rand(1000, 9000);
                                    $extension = $file->getClientOriginalExtension();
                                    $userEmail = auth()->user()->email;
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
                                ->deleteUploadedFileUsing(function () use ($action) {
                                    // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                    $user = auth()->user();
                                    $doc = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();

                                    if ($doc) {
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
                                ->disabled(function () use ($action) {
                                    $user = auth()->user();
                                    $doc_file = $user->userHasmanyDocEmp()
                                        ->where('file_name', $action->getName())
                                        ->first();
                                    return !empty($doc_file) ? 1 : 0;
                                })
                                ->afterStateUpdated(function ($state) {
                                    $this->updateStateInConfirm($state);
                                }),

                        ];
                    })
                    ->fillForm(function ($action): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();

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
                        ProcessEmpDocJob::dispatch($data, $user, $action->getLabel());
                    }),
            ])->label('อับโหลดเอกสาร')
                ->extraAttributes([
                    'style' => 'font-size: 1.3rem;',
                ])
                ->icon('heroicon-m-document-arrow-up')
                ->size(Size::ExtraLarge)
                ->color('primary')
                ->button(),

        ];
    }

    public function openFailedActionModal($actionId = null)
    {
        $this->mountAction($actionId);
    }
}
