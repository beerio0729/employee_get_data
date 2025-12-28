<?php

namespace App\Filament\Components;

use Detection\MobileDetect;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Jobs\ProcessEmpDocJob;
use Filament\Support\Enums\Size;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use App\Events\ProcessEmpDocEvent;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use App\Jobs\ProcessNoJsonEmpDocJob;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Components\UserFormComponent;
use Filament\Schemas\Components\Utilities\Set;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ActionFormComponent
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

    public bool $isSubmitDisabledFromFile = true;
    public bool $isSubmitDisabledFromConfirm = true;
    public bool $isMobile;
    public bool $isAndroidOS;
    public function __construct()
    {
        $detect = new MobileDetect();
        $this->isMobile = $detect->isMobile();
        $this->isAndroidOS = $detect->isAndroidOS();
    }

    public function getDocEmp($record, $action)
    {
        return $record->userHasmanyDocEmp()->where('file_name', $action->getName());
    }

    /**********ส่วนของ action component************* */

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
                    function () {
                        return $this->isSubmitDisabledFromFile || $this->isSubmitDisabledFromConfirm;
                    }
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
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public')
                        ->disk('public')
                        ->directory('emp_files')
                        ->required()
                        ->maxSize(3072)
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('2.8:3.5')
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();

                            return "{$record->id}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function ($set, $state, $get) {
                            $set('confirm', 0);
                            $this->isSubmitDisabledFromFile = blank($state);
                        })
                        ->afterStateHydrated(function () {
                            $this->isSubmitDisabledFromFile = true;
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
                        ->reactive()
                        ->afterStateHydrated(function () {
                            $this->isSubmitDisabledFromConfirm = true;
                        })
                        ->default(0)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
                        ->disabled(function ($record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
                        ->afterStateUpdated(function ($state, $get) {
                            $this->isSubmitDisabledFromConfirm = !$state;
                            $this->isSubmitDisabledFromFile = blank($get('image_profile'));
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

            ->action(function (array $data, $action, $livewire) {
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
                $livewire->dispatch('openActionModal', id: $action->getName());
            })
            ->extraModalFooterActions(
                function ($action) {
                    return [
                        DeleteAction::make($action->getName())
                            ->hidden(function ($record) use ($action) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                return blank($doc) ? 1 : 0;
                            })
                            ->label("ลบ" . $action->getLabel())
                            ->requiresConfirmation()
                            ->modalHeading("ลบ \"" . $action->getLabel() . "\" ทั้งหมด")
                            ->modalDescription("คุณต้องการลบ \"" . $action->getLabel() . "\" ใช่ไหม")
                            ->modalSubmitActionLabel('ยืนยันการลบ')
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }

                                $livewire->dispatch('refreshActionModal', id: $action->getName());
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
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
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
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->required()
                        ->maxSize(3072)
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();

                            return "{$record->id}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                        })

                        ->deleteUploadedFileUsing(function ($record, $livewire) use ($action) {
                            $record->userHasoneIdcard()->delete();
                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
                            $livewire->dispatch('refreshActionModal', id: $action->getName());
                        }),
                    Toggle::make('confirm')
                        ->label(new HtmlString($this->confirm))
                        ->accepted()
                        ->live()

                        ->default(false)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
                        ->disabled(function ($record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
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

            ->action(function (array $data, $action, $livewire) {

                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                    $livewire->dispatch('openActionModal', id: $action->getName());
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
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasoneIdcard()->delete();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $livewire->dispatch('refreshActionModal', id: $action->getName());
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
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
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
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->live()
                        ->directory('emp_files')
                        ->required()
                        ->maxSize(3072)
                        ->previewable(function ($state) {
                            $name = basename($state);
                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                            return $this->isAndroidOS && $extension === 'pdf' ? 0 : 1;
                        })
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();

                            return "{$record->id}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                        })

                        ->deleteUploadedFileUsing(function ($record, $livewire) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
                            $record->userHasoneResume()->delete();
                            $livewire->dispatch('refreshActionModal', id: $action->getName());
                        }),
                    Toggle::make('confirm')
                        ->label(new HtmlString($this->confirm))
                        ->accepted()
                        ->live()

                        ->default(false)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
                        ->disabled(function ($record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })

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

            ->action(function (array $data, $action, $livewire) {

                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                    $livewire->dispatch('openActionModal', id: $action->getName());
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
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $record->userHasoneResume()->delete();
                                $livewire->dispatch('refreshActionModal', id: $action->getName());
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
            ->label('วุฒิการศึกษา')
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
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
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
                        })
                        ->panelLayout(function () {
                            return $this->isMobile ? null : 'grid';
                        })
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->multiple()
                        ->maxSize(3072)
                        ->maxParallelUploads(1)
                        ->belowContent([
                            Icon::make(Heroicon::Star)->color('warning'),
                            "อับโหลดได้มากกว่า 1 {$action->getLabel()}",
                        ])
                        ->belowLabel([
                            Icon::make(Heroicon::OutlinedExclamationTriangle)->color('warning'),
                            "คำเตือน!!! หาก {$action->getLabel()} มีหลายหน้า ต้องทำให้เป็นไฟล์เดียวกันก่อนค่อยอับโหลด
                                    1 {$action->getLabel()} ต่อ 1 ไฟล์"
                        ])

                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();

                            return "{$record->id}/{$action->getName()}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                        })

                        ->deleteUploadedFileUsing(function ($state, $record, $livewire) use ($action) {

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
                            $livewire->dispatch('refreshActionModal', id: $action->getName());
                        }),
                    Toggle::make('confirm')
                        ->label(new HtmlString($this->confirm))
                        ->accepted()
                        ->live()
                        ->default(false)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
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

            ->action(function (array $data, $action, $livewire) {
                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                $fileForSend = array_values(array_diff($data[$action->getName()], $doc->path ?? []));

                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                    $livewire->dispatch('openActionModal', id: $action->getName());
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
                        array_reverse($fileForSend),
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
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasmanyTranscript()->delete();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $livewire->dispatch('refreshActionModal', id: $action->getName());
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
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
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
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
                        })
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->required()
                        ->maxSize(3072)
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();

                            return "{$record->id}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                        })

                        ->deleteUploadedFileUsing(function ($record, $livewire) use ($action) {
                            $record->userHasoneMilitary()->delete();
                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
                            $livewire->dispatch('refreshActionModal', id: $action->getName());
                        }),
                    Toggle::make('confirm')
                        ->label(new HtmlString($this->confirm))
                        ->accepted()
                        ->live()

                        ->default(false)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
                        ->disabled(function ($record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
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

            ->action(function (array $data, $action, $livewire) {

                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                    $livewire->dispatch('openActionModal', id: $action->getName());
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
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasoneMilitary()->delete();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $livewire->dispatch('refreshActionModal', id: $action->getName());
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
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
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
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
                        })
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->required()
                        ->maxSize(3072)
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();

                            return "{$record->id}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                        })

                        ->deleteUploadedFileUsing(function ($record, $livewire) use ($action) {
                            $record->userHasoneMarital()->delete();
                            $doc = $this->getDocEmp($record, $action)->first();

                            if (!blank($doc)) {
                                Storage::disk('public')->delete($doc->path);
                                $doc->delete();
                            }
                            $livewire->dispatch('refreshActionModal', id: $action->getName());
                        }),
                    Toggle::make('confirm')
                        ->label(new HtmlString($this->confirm))
                        ->accepted()
                        ->live()
                        ->default(false)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
                        ->disabled(function ($record) use ($action) {

                            $doc = $this->getDocEmp($record, $action)->first();
                            return !blank($doc) ? 1 : 0;
                        })
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

            ->action(function (array $data, $action, $livewire) {

                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                    $livewire->dispatch('openActionModal', id: $action->getName());
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
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasoneMarital()->delete();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $livewire->dispatch('refreshActionModal', id: $action->getName());
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
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
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
                        ->openable()
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
                        })
                        ->panelLayout(function () {
                            return $this->isMobile ? null : 'grid';
                        })
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->multiple()
                        ->maxSize(3072)
                        ->maxParallelUploads(1)
                        ->belowLabel([Icon::make(Heroicon::Star)->color('warning'), 'อับโหลดได้มากกว่า 1 ' . $action->getLabel()])
                        ->required()
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $i = mt_rand(1000, 9000);
                            $extension = $file->getClientOriginalExtension();

                            return "{$record->id}/{$action->getName()}/{$action->getName()}_{$i}.{$extension}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                        })
                        ->deleteUploadedFileUsing(function ($state, $record, $livewire) use ($action) {
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
                            $doc_certificate = $record->userHasoneCertificate()
                                ->where('file_path', $fileDelete[0])
                                ->first();
                            if (!blank($doc_certificate)) {
                                $doc_certificate->delete();
                            }
                            $livewire->dispatch('refreshActionModal', id: $action->getName());
                        }),
                    Toggle::make('confirm')
                        ->label(new HtmlString($this->confirm))
                        ->accepted()
                        ->live()

                        ->default(false)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
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

            ->action(function (array $data, $action, $livewire) {
                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                $fileForSend = array_values(array_diff($data[$action->getName()], $doc->path ?? []));

                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                    $livewire->dispatch('openActionModal', id: $action->getName());
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
                        array_reverse($fileForSend),
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
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasoneCertificate()->delete();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $livewire->dispatch('refreshActionModal', id: $action->getName());
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
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
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
                    (new UserFormComponent())->AnotherDocComponent($record, $action->getName()),
                    AdvancedFileUpload::make($action->getName())
                        ->removeUploadedFileButtonPosition('right')
                        ->openable()
                        ->label('อับโหลด' . $action->getLabel())
                        ->visibility('public') // เพื่อให้โหลดภาพได้ถ้าเก็บใน public
                        ->disk('public')
                        ->directory('emp_files')
                        ->multiple()
                        ->maxSize(3072)
                        ->maxParallelUploads(1)
                        ->required()
                        ->belowLabel([Icon::make(Heroicon::Star)->color('warning'), 'อับโหลดได้มากกว่า 1 ' . $action->getLabel()])
                        ->panelLayout(function () {
                            return $this->isMobile ? null : 'grid';
                        })
                        ->previewable(function () {
                            return $this->isAndroidOS ? 0 : 1;
                        })
                        ->validationMessages([
                            'required' => 'คุณยังไม่ได้อับโหลดเอกสารใดๆ กรุณาอับโหลดไฟล์ก่อนส่ง',
                            'max' => 'ไฟล์ต้องไม่เกิน 3 MB',
                        ])
                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $record) use ($action) {
                            $extension = $file->getClientOriginalExtension();
                            $name = $file->getClientOriginalName();

                            return "{$record->id}/{$action->getName()}/{$name}";
                        })
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->afterStateUpdated(function (Set $set, $state) {
                            $set('confirm', 0);
                        })

                        ->deleteUploadedFileUsing(function ($state, $record, $livewire) use ($action) {
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
                            $livewire->dispatch('refreshActionModal', id: $action->getName());
                        }),
                    Toggle::make('confirm')
                        ->label(new HtmlString($this->confirm))
                        ->accepted()
                        ->live()

                        ->default(false)
                        ->validationMessages([
                            'accepted' => 'กรุณากดยืนยันก่อนส่งเอกสาร',
                        ])
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
            ->action(function (array $data, $action, $livewire) {
                $user = auth()->user();
                $doc = $user->userHasmanyDocEmp()->where('file_name', $action->getName())->first();
                $fileForSend = array_values(array_diff($data[$action->getName()], $doc->path ?? []));

                if ($doc?->path === $data[$action->getName()]) {
                    Notification::make()
                        ->title('แก้ไขข้อมูลเรียบร้อยแล้ว')
                        ->color('success')
                        ->send();
                    $livewire->dispatch('openActionModal', id: $action->getName());
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
                        array_reverse($fileForSend),
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
                            ->action(function ($record, $action, $livewire) {
                                $doc = $this->getDocEmp($record, $action)->first();
                                $record->userHasmanyAnotherDoc()->delete();
                                if (!blank($doc)) {
                                    Storage::disk('public')->delete($doc->path);
                                    $doc->delete();
                                }
                                $livewire->dispatch('refreshActionModal', id: $action->getName());
                            })
                            ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว')

                    ];
                }
            );
    }



    /************ปุ่มหลัก********** */

    public function uploadAllDocActionGroup(): ActionGroup
    {
        return
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
            ->button()
            ->dropdownWidth(Width::Full)
            ->dropdownAutoPlacement()
            ->hidden(fn() => $this->isMobile ? 1 : 0)
        ;
    }

    public function addtionalAction(): Action
    {
        return
            Action::make('info')
            ->record(auth()->user())
            ->mountUsing(function (Schema $form, $record) {
                $form->fill($record->attributesToArray());
            })
            ->size(Size::ExtraLarge)
            ->iconSize('xl')
            ->extraAttributes([
                'style' => "
                width: 100%;
                font-size: 1.2rem;
                "
            ])
            ->icon('heroicon-m-user')
            ->color('primary')
            ->label('ข้อมูลเพิ่มเติม')
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
            ->action(function ($action, $livewire) {
                $livewire->dispatch('openActionModal', id: $action->getName());
                Notification::make()
                    ->title('บันทึกข้อมูลเรียบร้อยแล้ว')
                    ->color('success')
                    ->send();
            });
    }

    public function downloadPDFAction(): Action
    {
        return
            Action::make('pdf')
            ->record(auth()->user())
            ->label('ดาวน์โหลดใบสมัคร')
            ->icon(new HtmlString('
                <svg fill="currentColor" class="fi-icon fi-size-xl" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                    <path d="M208 48L96 48c-8.8 0-16 7.2-16 16l0 384c0 8.8 7.2 16 16 16l80 0 0 48-80 0c-35.3 0-64-28.7-64-64L32 64C32 28.7 60.7 0 96 0L229.5 0c17 0 33.3 6.7 45.3 18.7L397.3 141.3c12 12 18.7 28.3 18.7 45.3l0 149.5-48 0 0-128-88 0c-39.8 0-72-32.2-72-72l0-88zM348.1 160L256 67.9 256 136c0 13.3 10.7 24 24 24l68.1 0zM240 380l32 0c33.1 0 60 26.9 60 60s-26.9 60-60 60l-12 0 0 28c0 11-9 20-20 20s-20-9-20-20l0-128c0-11 9-20 20-20zm32 80c11 0 20-9 20-20s-9-20-20-20l-12 0 0 40 12 0zm96-80l32 0c28.7 0 52 23.3 52 52l0 64c0 28.7-23.3 52-52 52l-32 0c-11 0-20-9-20-20l0-128c0-11 9-20 20-20zm32 128c6.6 0 12-5.4 12-12l0-64c0-6.6-5.4-12-12-12l-12 0 0 88 12 0zm76-108c0-11 9-20 20-20l48 0c11 0 20 9 20 20s-9 20-20 20l-28 0 0 24 28 0c11 0 20 9 20 20s-9 20-20 20l-28 0 0 44c0 11-9 20-20 20s-20-9-20-20l0-128z"/>
                </svg>
            '))
            ->size(Size::ExtraLarge)
            ->iconSize('xl')
            ->extraAttributes([
                'style' => "
                width: 100%;
                font-size: 1.2rem;
                "
            ])
            ->color('primary')
            ->action(function ($record) {
                $missing = $this->checkDocDownloaded();
                $parts = [];

                $hasUpload = filled($missing['upload']);
                $hasInput  = filled($missing['input']);


                if ($hasUpload) {
                    $parts[] = 'คุณยังไม่ได้อัปโหลด: <br>"' . implode(', ', $missing['upload']) . '"';
                }

                if ($hasInput) {
                    $parts[] = 'คุณยังไม่ได้กรอกข้อมูลเพิ่มเติมในหัวข้อ: <br>"' . implode(', ', $missing['input']) . '"';
                }

                // ประโยคปิดท้าย
                if ($hasUpload || $hasInput) {
                    if ($hasUpload && $hasInput) {
                        $ending = 'กรุณาอัปโหลดเอกสาร และ กรอกข้อมูลดังกล่าว<br>ก่อนดาวน์โหลดใบสมัคร';
                    } elseif ($hasUpload) {
                        $ending = 'กรุณาอัปโหลดเอกสารก่อนดาวน์โหลดใบสมัคร';
                    } else {
                        $ending = 'กรุณากรอกข้อมูลดังกล่าวก่อนดาวน์โหลดใบสมัคร';
                    }

                    $msg = implode('<br><br>', $parts) . '<br><br>' . $ending;

                    event(new ProcessEmpDocEvent($msg, $record, 'popup', null, false));
                } else {
                    $record->userHasoneApplicant()->update([
                        'status_id' => 2,
                    ]);
                    return redirect('/pdf');
                }
            });
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
            'รูปโปรไฟล์' => $user->userHasmanyDocEmp()->where('file_name', 'image_profile')->exists(),
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
}
