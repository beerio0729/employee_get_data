<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use App\Jobs\ProcessEmpDocJob;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Filament\Pages\Dashboard as BaseDashboard;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Dashboard extends BaseDashboard
{
    //use HasFiltersForm;

    public function getActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('emp_image')
                    ->label('รูปโปรไฟล์')
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
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/image_profile.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component, $state) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', 'image_profile')->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->deleteUploadedFileUsing(function ($file, $record) {
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
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();

                        if (!empty($data['image_profile'])) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'image_profile'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['image_profile'],
                                ]
                            );
                        }
                        Notification::make()
                            ->title('อับโหลด image_profile แล้ว')
                            ->success()
                            ->send();
                    }),
                Action::make('resume')
                    ->label('Resume')
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
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/resume.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component, $state) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', 'resume')->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->deleteUploadedFileUsing(function ($file, $record) {
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
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();

                        if (!empty($data['resume'])) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'resume'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['resume'],
                                ]
                            );
                        }
                        dump($data);
                        ProcessEmpDocJob::dispatch($data, $user);
                    }),
                Action::make('idcard')
                    ->label('บัตรประชาชน')
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
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/idcard.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component, $state) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', 'idcard')->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->deleteUploadedFileUsing(function ($file, $record) {
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
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();

                        if (!empty($data['idcard'])) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'idcard'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['idcard'],
                                ]
                            );
                        }
                        ProcessEmpDocJob::dispatch($data, $user);
                    }),
                Action::make('transcript')
                    ->label('ใบแสดงผลการศึกษา')
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
                            //->panelLayout('grid')
                            ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                            ->disk('public')
                            ->directory('emp_files')
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $state) {
                                
                                $i = mt_rand(1000,9000);
                                $extension = $file->getClientOriginalExtension();
                                $userEmail = auth()->user()->email;
                                return "{$userEmail}/transcript_{$i}.{$extension}";
                            })
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateHydrated(function ($component, $state) {
                                $user = auth()->user();
                                $doc = $user->userHasmanyDocEmp()->where('file_name', 'transcript')->first();
                                $component->state($doc ? $doc->path : null);
                            })
                            ->deleteUploadedFileUsing(function ($file, $record) {
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
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();

                        if (!empty($data['transcript'])) {
                            $user->userHasmanyDocEmp()->updateOrCreate(
                                ['file_name' => 'idcard'],
                                [
                                    'user_id' => $user->id,
                                    'path' => $data['transcript'],
                                ]
                            );
                        }
                        
                        ProcessEmpDocJob::dispatch($data, $user);
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
