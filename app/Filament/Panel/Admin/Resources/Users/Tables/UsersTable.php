<?php

namespace App\Filament\Panel\Admin\Resources\Users\Tables;

use Carbon\Carbon;
use App\Models\Role;
use Filament\Tables\Table;
use Detection\MobileDetect;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use App\Services\LineSendMessageService;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\DateTimePicker;
use App\Models\WorkStatusDefination\WorkStatusDefinationDetail;

class UsersTable
{
    public static bool $isMobile;
    public static bool $isAndroidOS;

    public static function configure(Table $table): Table
    {
        $detect = new MobileDetect();
        self::$isMobile = $detect->isMobile();
        self::$isAndroidOS = $detect->isAndroidOS();
        return $table
            ->recordUrl(null) //ป้องกันไม่ให้กดที่ตารางแล้วแก้ไข้
            ->striped()
            ->columns([
                Split::make([
                    TextColumn::make('#')
                        ->alignEnd()
                        ->state(fn($rowLoop) => $rowLoop->parent->index + 1)
                        ->grow(false)
                        ->visibleFrom('sm')
                        ->extraAttributes(['style' => 'width:20px']),
                    ImageColumn::make('userHasoneResume.image')
                        ->disk('public')
                        //->simpleLightbox()
                        ->circular()
                        ->grow(false)
                        ->state((function ($record) {
                            $doc = $record->userHasmanyDocEmp()->where('file_name', 'image_profile')->first();
                            return $doc ? $doc->path : asset('storage/user.png');
                        }))
                        ->extraAttributes(['style' => 'width:50px']),
                    Stack::make([
                        TextColumn::make('userHasoneIdcard.name_th')->label('ชื่อ')->searchable()->sortable()
                            ->formatStateUsing(function ($record, $state) {
                                $user = $record->userHasoneIdcard;
                                return "{$user->prefix_name_th} {$state} {$user->last_name_th}";
                            }),
                        TextColumn::make('userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail')->label('สถานะ')->searchable()->sortable()
                            ->formatStateUsing(function ($state) {
                                if (filled($state)) {
                                    $status_detail = $state->name_th; //$state จะไปตาราง status detail
                                    $status = $state->workStatusDefDetailBelongsToWorkStatusDef->name_th;
                                } else {
                                    $status = 'ไม่มีสถานะ';
                                    $status_detail = 'ไม่มีรายละเอียดสถานะ';
                                }
                                return $status . " : " . $status_detail;
                            }),

                    ]),
                    ...self::detailUserForDesktop(),
                ]),
                ...self::panelDetailUserForPhone(),

            ])
            ->filters([
                SelectFilter::make('work_status_id')
                    ->label('สถานะ')
                    ->relationship('userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail.workStatusDefDetailBelongsToWorkStatusDef', 'name_th')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status_detail_id')
                    ->label('รายละเอียดสถานะ')
                    ->options(function ($filter) {
                        $work_status_id = intval($filter->getTable()->getFilter('work_status_id')->getState()['value']);
                        return WorkStatusDefinationDetail::where('work_status_def_id', $work_status_id)->pluck('name_th', 'id');
                    })
                    ->query(function ($query, $filter, $livewire) {
                        $work_status_id_detail = $filter->getTable()->getFilter('status_detail_id')->getState()['value'];

                        if (filled($work_status_id_detail)) {
                            if ($work_status_id_detail !== '3') {
                                $livewire->tableFilters['interview_at_filter']['interview_at'] = null;
                            } $query->whereRelation('userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail', 'id', intval($work_status_id_detail));
                        } else {
                            $livewire->tableFilters['interview_at_filter']['interview_at'] = null;
                        }
                    })
                    ->preload()
                    ->searchable(),
                Filter::make('interview_at_filter')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('interview_at')
                            ->label('เวลานัดสัมภาณษ์')
                            ->visible(function ($state, $livewire) {
                                $status = $livewire->tableFilters['status_detail_id']['value'] ?? null;
                                return $status === '3';
                            })
                            ->seconds(false)
                        //DateTimePicker::make('created_until'),
                    ])->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['interview_at'] ?? null,
                            function (Builder $query, $value) {
                                $carbonValue = Carbon::parse($value);
                                $date = $carbonValue->toDateString();   // YYYY-MM-DD
                                $time = $carbonValue->format('H:i');   // HH:MM

                                $query->whereHas('userHasoneWorkStatus.workStatusHasonePreEmp', function (Builder $q) use ($date, $time) {
                                    $q->whereDate('interview_at', $date)
                                        ->whereTime('interview_at', '>=', '00:01')
                                        ->whereTime('interview_at', '<=', $time);
                                });
                            }
                        );
                    })
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->deferFilters(false)
            ->recordActions([
                ActionGroup::make([
                    Action::make('interview')
                        ->label('นัดหมายวันสัมภาษณ์')
                        ->mountUsing(function (Schema $form, $record) {
                            $form->fill($record->userHasoneWorkStatus->workStatusHasonePreEmp->attributesToArray());
                        })
                        ->visible(
                            function ($record) {
                                $work_state_detail = $record->userHasoneWorkStatus?->workStatusBelongToWorkStatusDefDetail;
                                if ($work_state_detail->work_phase === "pre_interview_time") {
                                    return 0;
                                }
                                return 1;
                            }
                        )
                        ->color('success')
                        ->icon('heroicon-m-calendar')
                        ->modalSubmitActionLabel('อับเดตข้อมูล')
                        ->modalSubmitAction(function ($action, $record) {
                            $action->disabled(
                                function () use ($record) {
                                    return filled($record->userHasoneWorkStatus->workStatusHasonePreEmp->interview_at);
                                }
                            );
                        })
                        ->modalHeading('นัดหมายวันสัมภาษณ์')
                        ->modalWidth(Width::Medium)
                        ->closeModalByClickingAway(false)
                        ->schema([
                            DateTimePicker::make('interview_at')
                                ->hiddenLabel()
                                ->required()
                                ->validationMessages(['required' => 'กรุณาเลือกวันเวลานัดสัมภาษณ์'])
                                ->native(false)
                                ->placeholder('วันเวลาในการนัดสัมภาษณ์')
                                ->displayFormat('d M Y | เวลา H:i')
                                ->seconds(false)
                                ->locale('th')
                                ->buddhist(),
                        ])
                        ->action(function ($record, array $data) {
                            $view_notification = 'view_interview_' . Date::now()->timestamp;
                            $workStatus = $record->userHasoneWorkStatus()->first();
                            $workStatus->update([
                                'work_status_def_detail_id' => 3,
                            ]);

                            $workStatus->workStatusHasonePreEmp()->update([
                                'interview_at' => $data['interview_at'],
                            ]);
                            Notification::make() //ต้องรัน Queue
                                ->title('แจ้งวันนัดสัมภาษณ์')
                                ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                                    . "<br><br>ทางบริษัทฯ ขอแจ้งนัดหมายวันสัมภาษณ์งานของท่าน<br>ในวันที่
                                <B>"
                                    . Carbon::parse($data['interview_at'])->locale('th')->translatedFormat('d M ')
                                    . (Carbon::parse($data['interview_at'])->year + 543)
                                    . "\nเวลา "
                                    . Carbon::parse($data['interview_at'])->format(' H:i')
                                    . " น."
                                    . "</B>"
                                    . "<br><br>โปรดเตรียมเอกสารที่เกี่ยวข้องและมาถึงก่อนเวลานัดหมาย 10 นาที"
                                    . "<br>ขอบคุณค่ะ")
                                ->actions([
                                    Action::make($view_notification)
                                        ->button()
                                        ->label('ทำเครื่องหมายว่าอ่านแล้ว')
                                        ->markAsRead(),
                                ])
                                ->sendToDatabase($record, isEventDispatched: true);

                            LineSendMessageService::send($record->provider_id, [
                                "เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                                    . "ทางบริษัทฯ ขอแจ้งนัดหมายวันสัมภาษณ์งานของท่าน\n\nในวันที่ "
                                    . Carbon::parse($data['interview_at'])->locale('th')->translatedFormat('d M ')
                                    . (Carbon::parse($data['interview_at'])->year + 543)
                                    . "\nเวลา "
                                    . Carbon::parse($data['interview_at'])->format(' H:i')
                                    . " น.\n\n"
                                    . "โปรดเตรียมเอกสารที่เกี่ยวข้องและมาถึงก่อนเวลานัดหมาย 10 นาที \n\n"
                                    . "ขอบคุณค่ะ",
                            ]);
                            Notification::make()
                                ->title('นัดหมายวันสัมภาษณ์เรียบร้อยแล้ว')
                                ->success()
                                ->send();
                        })
                        ->extraModalFooterActions([
                            Action::make('cancel_interview')
                                ->visible(fn($record) => $record->userHasoneWorkStatus->work_status_def_detail_id === 3)
                                ->requiresConfirmation()
                                ->color('danger')
                                ->label('ยกเลิกนัดสัมภาษณ์')
                                ->modalHeading("ยกเลิกนัดสัมภาษณ์")
                                ->modalDescription("คุณต้องการยกเลิกนัดสัมภาษณ์ใช่หรือไม่")
                                ->modalSubmitActionLabel('ยืนยัน')
                                ->action(function ($record) {
                                    $view_notification = 'view_interview_' . Date::now()->timestamp;
                                    $workStatus = $record->userHasoneWorkStatus()->first();
                                    $interview_date = $workStatus?->workStatusHasonePreEmp?->interview_at;
                                    $workStatus->update([
                                        'work_status_def_detail_id' => 2,
                                    ]);
                                    $workStatus->workStatusHasonePreEmp()->update([
                                        'interview_at' => null,
                                    ]);
                                    Notification::make() //ต้องรัน Queue
                                        ->title('แจ้งวันนัดสัมภาษณ์')
                                        ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                                            . "<br><br>ทางบริษัทฯ ขอแจ้ง<br>❌ ยกเลิกนัดหมายวันสัมภาษณ์งานของท่าน<br>ในวันที่
                                            <B>"
                                            . Carbon::parse($interview_date)->locale('th')->translatedFormat('d M ')
                                            . (Carbon::parse($interview_date)->year + 543)
                                            . "\nเวลา "
                                            . Carbon::parse($interview_date)->format(' H:i')
                                            . " น.\n\n"
                                            . "</B>"
                                            . "<br><br>ขออภัยมา ณ ที่นี้")
                                        ->actions([
                                            Action::make($view_notification)
                                                ->button()
                                                ->label('ทำเครื่องหมายว่าอ่านแล้ว')
                                                ->markAsRead(),
                                        ])
                                        ->sendToDatabase($record, isEventDispatched: true);
                                    LineSendMessageService::send($record->provider_id, [
                                        "เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                                            . "ทางบริษัทฯ ขอแจ้ง 
                                            \n❌ ยกเลิกนัดหมายวันสัมภาษณ์งานของท่าน\n\nในวันที่ "
                                            . Carbon::parse($interview_date)->locale('th')->translatedFormat('d M ')
                                            . (Carbon::parse($interview_date)->year + 543)
                                            . "\nเวลา "
                                            . Carbon::parse($interview_date)->format(' H:i')
                                            . " น.\n\n"
                                            . "ขออภัยมา ณ ที่นี้",
                                    ]);
                                })

                                ->cancelParentActions()
                                ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                        ]),
                    Action::make('role_id')
                        ->mountUsing(function (Schema $form, $record) {
                            $form->fill($record->attributesToArray());
                        })
                        //->disabled(fn($record) => !$record->userHasManyPostEmp()->exists()) // รอจัดการเรื่องพนักงาน
                        ->visible(fn() => auth()->user()->role_id === 1)
                        ->color(fn($record) => $record->role_id === 3 ? 'info' : 'gray')
                        ->label('สิทธิ์การเข้าถึง')
                        ->icon(Heroicon::UserGroup)
                        ->modalSubmitActionLabel('กำหนดสิทธิ์')
                        ->modalHeading('เลือกระดับสิทธิ์')
                        ->modalWidth(Width::Medium)
                        ->closeModalByClickingAway(false)
                        ->schema([
                            Select::Make('role_id')
                                ->hiddenLabel()
                                ->options(function ($record) {
                                    return Role::where('active', 1)->pluck('name_th', 'id');
                                })
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'role_id' => $data['role_id'],
                            ]);
                            $emp_status = $record->userBelongToRole->name_th;
                            Notification::make()
                                ->title("เปลี่ยนสถานะเป็น \"{$emp_status}\" แล้ว")
                                ->success()
                                ->send();
                        }),

                    ViewAction::make()
                        ->label('ดูรายละเอียด')->icon('heroicon-m-eye')
                        ->color('warning')->modalWidth('7xl')->modalHeading('รายละเอียดข้อมูล'),
                    DeleteAction::make()->label('ลบพนักงาน'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_as_interviewed')
                        ->icon(Heroicon::ChatBubbleLeftRight)
                        ->label('มาสัมภาษณ์แล้ว')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->userHasoneWorkStatus->update([
                                    'work_status_def_detail_id' => 4,
                                ]);
                            }
                        })
                        ->requiresConfirmation() // จะมี popup confirm
                        ->color('success'), // สีเขียว
                ]),
            ]);
    }

    /*************ส่วนกำหนดการแสดงผลระหว่างโทรศัพท์ หรือคอม************ */

    public static function detailUserComponent(): array //ก้อนของรายละเอียด
    {
        return [
            Stack::make([
                TextColumn::make('email')
                    ->label('อีเมล')
                    ->searchable()->sortable()
                    ->icon('heroicon-m-envelope')
                    ->iconColor('warning')
                    ->copyable()
                    ->copyMessage('คัดลอกแล้ว')->copyMessageDuration(1500)->searchable()->sortable(),
                TextColumn::make('tel')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->iconColor('primary')
                    ->default('ไม่ได้ระบุ')
                    ->url(fn($record) => 'tel:' . $record->userHasoneResume->tel),
            ])->space(1),
            TextColumn::make('userHasoneWorkStatus.workStatusHasonePreEmp.interview_at')
                ->searchable()->sortable()
                ->label('วันนัดสัมภาษณ์')
                ->buddhistDate('d M Y H:i')
                ->prefix(new HtmlString('<div><strong>วันที่นัดสัมภาษณ์ :</strong></div>'))
                ->visible(fn($record) => $record?->isPreEmployment()),
            // Stack::make([
            //     TextColumn::make('userHasManyPostEmp.employeeBelongToDepartment.name')
            //         ->searchable()->sortable()
            //         ->label('แผนก'),
            //     TextColumn::make('userHasManyPostEmp.employeeBelongToPosition.name')
            //         ->searchable()->sortable()
            //         ->label('ตำแหน่ง'),
            // ])->space(1)->visible(fn($record) =>fn($record) => $record?->isPostEmployment()),
        ];
    }

    public static function panelDetailUserComponent(): array //panel สำหรับมือถือ
    {
        return [
            Panel::make([
                Split::make([
                    ...self::detailUserComponent()
                ])->From('sm')
            ])->collapsed()
        ];
    }

    protected static function detailUserForDesktop(): array
    {
        return self::$isMobile ? [] : self::detailUserComponent();
    }

    protected static function panelDetailUserForPhone()
    {
        if (self::$isMobile) {
            return self::panelDetailUserComponent();
        } else {
            return [];
        }
    }
}
