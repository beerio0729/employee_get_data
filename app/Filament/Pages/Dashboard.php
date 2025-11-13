<?php

namespace App\Filament\Pages;


use Closure;
use Filament\Actions\Action;
use App\Jobs\ProcessEmpDocJob;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;
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

    public function getActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('emp_image')
                    ->label('รูปโปรไฟล์')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel(
                        fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'image_profile')->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'image_profile')->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'image_profile')->exists()
                        ? 'success'
                        : 'warning')
                    ->schema([
                        AdvancedFileUpload::make('image_profile')
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
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                $i = mt_rand(1000, 9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/image_profile_{$i}.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateUpdated(function (Set $set) {
                                $set('confirm', 0);
                            })
                            ->deleteUploadedFileUsing(function () {
                                // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'image_profile')
                                    ->first();

                                if ($doc) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                            }),
                        Checkbox::make('confirm')
                            ->label(new HtmlString($this->confirm))
                            //->accepted()
                            ->default(false)
                            ->validationMessages([
                                'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                            ])
                            ->disabled(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'image_profile')
                                    ->first();
                                if (empty($doc_file)) {
                                    return empty($get('image_profile')) ? 1 : 0;
                                } else {
                                    return 1;
                                }
                            })
                            ->hidden(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'image_profile')
                                    ->first();
                                return empty($get('image_profile')) ? 1 : 0;
                            })
                    ])
                    ->fillForm(function (): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', 'image_profile')->first();

                        // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (emp_image)
                        return [
                            'image_profile' => $doc ? $doc->path : null,
                            'confirm' => $doc ? $doc->confirm : false,
                        ];
                    })

                    ->action(function (array $data) {
                        $user = auth()->user();

                        if (!empty($data['image_profile'])) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'image_profile'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['image_profile'],
                                    'confirm' => $data['confirm'],
                                ]
                            );
                        }
                        return Redirect("/profile?tab=resume::data::tab");
                    }),
                Action::make('resume')
                    ->label('Resume')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel(
                        fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'resume')->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'resume')->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'resume')->exists()
                        ? 'success'
                        : 'warning')
                    ->schema([
                        AdvancedFileUpload::make('resume')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->required()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            ])
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                $i = mt_rand(1000, 9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/resume_{$i}.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateUpdated(function (Set $set) {
                                $set('confirm', 0);
                            })
                            ->deleteUploadedFileUsing(function () {
                                // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'resume')
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
                        Checkbox::make('confirm')
                            ->label(new HtmlString($this->confirm))
                            ->accepted()
                            ->default(false)
                            ->validationMessages([
                                'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                            ])
                            ->disabled(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'resume')
                                    ->first();
                                if (empty($doc_file)) {
                                    return empty($get('resume')) ? 1 : 0;
                                } else {
                                    return 1;
                                }
                            })
                            ->hidden(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'resume')
                                    ->first();
                                return empty($get('resume')) ? 1 : 0;
                            })
                    ])
                    ->fillForm(function (): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', 'resume')->first();

                        // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (resume)
                        return [
                            'resume' => $doc ? $doc->path : null,
                            'confirm' => $doc ? $doc->confirm : false,
                        ];
                    })
                    ->action(function (array $data) {
                        $user = auth()->user();
                        $doc_file = $user->userHasmanyDocEmp()
                            ->where('file_name', 'resume')
                            ->first();

                        if (empty($doc_file)) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'resume'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['resume'],
                                    'confirm' => $data['confirm'],
                                ]
                            );
                            ProcessEmpDocJob::dispatch($data, $user);
                        } else {
                            if ($data['resume'] === $doc_file->path) {
                                return Notification::make()
                                    ->title('ยังไม่ได้อับเดตเอกสาร')
                                    ->body('คุณยังไม่ได้อับเดตเอกสารใหม่ ต้องลบเอกสารเก่าและอับโหลดเอกสารใหม่ก่อนดำเนินการต่อ')
                                    ->danger()
                                    ->send();
                                return;
                            } else {
                                $user->userHasmanyDocEmp()->updateOrCreate(
                                    ['file_name' => 'resume'],
                                    [
                                        'user_id' => $user->id,
                                        'path' => $data['resume'],
                                        'confirm' => $data['confirm'],
                                    ]
                                );
                                ProcessEmpDocJob::dispatch($data, $user);
                            }
                        }
                    }),
                Action::make('idcard')
                    ->label('บัตรประชาชน')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel(
                        fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'idcard')->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'idcard')->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'idcard')->exists()
                        ? 'success'
                        : 'warning')
                    ->schema([
                        AdvancedFileUpload::make('idcard')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->required()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            ])
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                $i = mt_rand(1000, 9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/idcard_{$i}.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateUpdated(function (Set $set) {
                                $set('confirm', 0);
                            })
                            ->deleteUploadedFileUsing(function () {
                                // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                $user = auth()->user();
                                //dump($user->id);
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'idcard')
                                    ->first();
                                //dump($user->userHasoneIdcard()->get());
                                $doc_relation = $user->userHasoneIdcard()
                                    ->where('user_id', $user->id)->first();;
                                if ($doc_file) {
                                    Storage::disk('public')->delete($doc_file->path);
                                    $doc_file->delete();
                                    // 3.1 HasOne Relations (Location, JobPreference)
                                    if ($doc_relation) {
                                        $doc_relation->delete();
                                    }
                                }
                            }),
                        Checkbox::make('confirm')
                            ->label(new HtmlString($this->confirm))
                            ->accepted()
                            ->default(false)
                            ->validationMessages([
                                'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                            ])
                            ->disabled(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'idcard')
                                    ->first();
                                if (empty($doc_file)) {
                                    return empty($get('idcard')) ? 1 : 0;
                                } else {
                                    return 1;
                                }
                            })
                            ->hidden(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'idcard')
                                    ->first();
                                return empty($get('idcard')) ? 1 : 0;
                            })
                    ])
                    ->fillForm(function (): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', 'idcard')->first();

                        // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (idcard)
                        return [
                            'idcard' => $doc ? $doc->path : null,
                            'confirm' => $doc ? $doc->confirm : false,
                        ];
                    })
                    ->action(function (array $data) {
                        $user = auth()->user();
                        $doc_file = $user->userHasmanyDocEmp()
                            ->where('file_name', 'idcard')
                            ->first();

                        if (empty($doc_file)) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'idcard'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['idcard'],
                                    'confirm' => $data['confirm'],
                                ]
                            );
                            ProcessEmpDocJob::dispatch($data, $user);
                        } else {
                            if ($data['idcard'] === $doc_file->path) {
                                return Notification::make()
                                    ->title('ยังไม่ได้อับเดตเอกสาร')
                                    ->body('คุณยังไม่ได้อับเดตเอกสารใหม่ ต้องลบเอกสารเก่าและอับโหลดเอกสารใหม่ก่อนดำเนินการต่อ')
                                    ->danger()
                                    ->send();
                                return;
                            } else {
                                $user->userHasmanyDocEmp()->updateOrCreate(
                                    ['file_name' => 'idcard'],
                                    [
                                        'user_id' => $user->id,
                                        'path' => $data['idcard'],
                                        'confirm' => $data['confirm'],
                                    ]
                                );
                                ProcessEmpDocJob::dispatch($data, $user);
                            }
                        }
                    }),
                Action::make('transcript')
                    ->label('ใบแสดงผลการศึกษา')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel(
                        fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'transcript')->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'transcript')->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'transcript')->exists()
                        ? 'success'
                        : 'warning')
                    ->schema([
                        AdvancedFileUpload::make('transcript')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->multiple()
                            
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->required()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            ])
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $state) {

                                $i = mt_rand(1000, 9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/transcript_{$i}.{$extension}";
                            })
                            ->afterStateUpdated(function (Set $set) {
                                $set('confirm', 0);
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->deleteUploadedFileUsing(function ($component) {
                                // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                $user = auth()->user();
                                //dump($user->id);
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'transcript')
                                    ->first();
                                //dump($user->userHasonetranscript()->get());
                                $doc_relation = $user->userHasonetranscript()
                                    ->where('user_id', $user->id)->first();;
                                if ($doc_file) {
                                    Storage::disk('public')->delete($doc_file->path);
                                    $doc_file->delete();
                                    // 3.1 HasOne Relations (Location, JobPreference)
                                    if ($doc_relation) {
                                        $doc_relation->delete();
                                    }
                                }
                            }),
                        Checkbox::make('confirm')
                            ->label(new HtmlString($this->confirm))
                            ->accepted()
                            ->default(false)
                            ->validationMessages([
                                'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                            ])
                            ->disabled(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'transcript')
                                    ->first();
                                if (empty($doc_file)) {
                                    return empty($get('transcript')) ? 1 : 0;
                                } else {
                                    return 1;
                                }
                            })
                            ->hidden(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'transcript')
                                    ->first();
                                return empty($get('transcript')) ? 1 : 0;
                            })
                    ])
                    ->fillForm(function (): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', 'transcript')->first();

                        // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (transcript)
                        return [
                            'transcript' => $doc ? $doc->path : null,
                            'confirm' => $doc ? $doc->confirm : false,
                        ];
                    })
                    ->action(function (array $data) {
                        $user = auth()->user();
                        $doc_file = $user->userHasmanyDocEmp()
                            ->where('file_name', 'transcript')
                            ->first();

                        if (empty($doc_file)) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'transcript'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['transcript'],
                                    'confirm' => $data['confirm'],
                                ]
                            );
                            ProcessEmpDocJob::dispatch($data, $user);
                        } else {
                            if ($data['transcript'] === $doc_file->path) {
                                return Notification::make()
                                    ->title('ยังไม่ได้อับเดตเอกสาร')
                                    ->body('คุณยังไม่ได้อับเดตเอกสารใหม่ ต้องลบเอกสารเก่าและอับโหลดเอกสารใหม่ก่อนดำเนินการต่อ')
                                    ->danger()
                                    ->send();
                                return;
                            } else {
                                $user->userHasmanyDocEmp()->updateOrCreate(
                                    ['file_name' => 'transcript'],
                                    [
                                        'user_id' => $user->id,
                                        'path' => $data['transcript'],
                                        'confirm' => $data['confirm'],
                                    ]
                                );
                                ProcessEmpDocJob::dispatch($data, $user);
                            }
                        }
                    }),
                Action::make('bookbank')
                    ->label('สมุดบัญชีธนาคาร')
                    ->closeModalByClickingAway(false)
                    ->modalSubmitActionLabel(
                        fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'bookbank')->exists()
                            ? 'อับเดตข้อมูล'
                            : 'อับโหลดข้อมูล'
                    )
                    ->button()
                    ->icon(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'bookbank')->exists()
                        ? 'heroicon-m-check-circle'
                        : 'heroicon-m-exclamation-triangle')
                    ->color(fn() => auth()->user()->userHasmanyDocEmp()->where('file_name', 'bookbank')->exists()
                        ? 'success'
                        : 'warning')
                    ->schema([
                        AdvancedFileUpload::make('bookbank')
                            ->pdfPreviewHeight(400) // Customize preview height
                            ->pdfDisplayPage(1) // Set default page
                            ->pdfToolbar(true) // Enable toolbar
                            ->pdfZoomLevel(100) // Set zoom level
                            ->pdfFitType(PdfViewFit::FIT) // Set fit type
                            ->pdfNavPanes(true) // Enable navigation panes
                            ->label('เลือกไฟล์')
                            ->openable()
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->required()
                            ->validationMessages([
                                'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            ])
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $state) {
                                $i = mt_rand(1000, 9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/bookbank_{$i}.{$extension}";
                            })
                            ->afterStateUpdated(function (Set $set) {
                                $set('confirm', 0);
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->deleteUploadedFileUsing(function ($component) {
                                // ลบไฟล์จริงใน storage และลบ record ใน DB ด้วย
                                $user = auth()->user();
                                //dump($user->id);
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'bookbank')
                                    ->first();
                                //dump($user->userHasonebookbank()->get());
                                $doc_relation = $user->userHasonebookbank()
                                    ->where('user_id', $user->id)->first();;
                                if ($doc_file) {
                                    Storage::disk('public')->delete($doc_file->path);
                                    $doc_file->delete();
                                    // 3.1 HasOne Relations (Location, JobPreference)
                                    if ($doc_relation) {
                                        $doc_relation->delete();
                                    }
                                }
                            }),
                        Checkbox::make('confirm')
                            ->label(new HtmlString($this->confirm))
                            ->accepted()
                            ->default(false)
                            ->validationMessages([
                                'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                            ])
                            ->disabled(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'bookbank')
                                    ->first();
                                if (empty($doc_file)) {
                                    return empty($get('bookbank')) ? 1 : 0;
                                } else {
                                    return 1;
                                }
                            })
                            ->hidden(function (Get $get) {
                                $user = auth()->user();
                                $doc_file = $user->userHasmanyDocEmp()
                                    ->where('file_name', 'bookbank')
                                    ->first();
                                return empty($get('bookbank')) ? 1 : 0;
                            })
                    ])
                    ->fillForm(function (): array {
                        $user = auth()->user();
                        $doc = $user->userHasmanyDocEmp()->where('file_name', 'bookbank')->first();

                        // ต้อง Return Array โดย Key ต้องตรงกับชื่อ Field (bookbank)
                        return [
                            'bookbank' => $doc ? $doc->path : null,
                            'confirm' => $doc ? $doc->confirm : false,
                        ];
                    })
                    ->action(function (array $data) {
                        $user = auth()->user();
                        $doc_file = $user->userHasmanyDocEmp()
                            ->where('file_name', 'bookbank')
                            ->first();

                        if (empty($doc_file)) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'bookbank'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['bookbank'],
                                    'confirm' => $data['confirm'],
                                ]
                            );
                            ProcessEmpDocJob::dispatch($data, $user);
                        } else {
                            if ($data['bookbank'] === $doc_file->path) {
                                return Notification::make()
                                    ->title('ยังไม่ได้อับเดตเอกสาร')
                                    ->body('คุณยังไม่ได้อับเดตเอกสารใหม่ ต้องลบเอกสารเก่าและอับโหลดเอกสารใหม่ก่อนดำเนินการต่อ')
                                    ->danger()
                                    ->send();
                                return;
                            } else {
                                $user->userHasmanyDocEmp()->updateOrCreate(
                                    ['file_name' => 'bookbank'],
                                    [
                                        'user_id' => $user->id,
                                        'path' => $data['bookbank'],
                                        'confirm' => $data['confirm'],
                                    ]
                                );
                                ProcessEmpDocJob::dispatch($data, $user);
                            }
                        }
                    }),
            ])->label('อับโหลดเอกสาร')
                ->extraAttributes([
                    'style' => 'font-size: 1.2rem;',
                ])
                ->icon('heroicon-m-document-arrow-up')
                ->size(Size::Large)
                ->color('primary')
                ->button(),

        ];
    }
}






                    // ->disabled(fn() => auth()->user()
                    //     ->userHasmanyDocEmp()
                    //     ->where('file_name', 'resume')
                    //     ->exists())
                    
                                            // Select::make('file_name')
                        //     ->hiddenlabel()
                        //     ->placeholder('กรุณาเลือกประเภทเอกสาร')
                        //     ->required()
                        //     ->options(function () {
                        //         $options = config('iconf.doc_name');

                        //         $user = auth()->user(); // หรือจะใช้ $record->user ก็ได้
                        //         $uploadedDocs = $user->userHasmanyDocEmp->pluck('file_name')->toArray();


                        //         // filter เอาออก
                        //         return collect($options)
                        //             ->reject(fn($label, $key) => in_array($key, $uploadedDocs))
                        //             ->toArray();
                        //     })
                        //     ->label('เลือกเอกสาร'),
