<?php

namespace App\Filament\Panel\Admin\Resources\Users\Tables;

use Carbon\Carbon;
use App\Models\Role;
use Filament\Tables\Table;
use Detection\MobileDetect;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use App\Services\LineSendMessageService;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use App\Models\Organization\OrganizationStructure;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\Components\Repeater\TableColumn;
use App\Models\WorkStatusDefination\WorkStatusDefination;
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
                        TextColumn::make('userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail.name_th')
                            ->label('สถานะ')
                            ->formatStateUsing(function ($state, $record) {
                                $detail = $record->userHasoneWorkStatus?->workStatusBelongToWorkStatusDefDetail;
                                $status = $detail?->workStatusDefDetailBelongsToWorkStatusDef?->name_th;

                                return ($status ?? 'ไม่มีสถานะ') . ' : ' . ($detail->name_th ?? 'ไม่มีรายละเอียดสถานะ');
                            })

                    ]),
                    ...self::detailUserForDesktop(),
                ]),
                ...self::panelDetailUserForPhone(),

            ])
            ->filters([
                Filter::make('filter_component')
                    ->columnSpan(4)
                    ->columns(4)
                    ->schema([
                        Select::make('work_status_id')
                            ->label('เลือกสถานะ')
                            ->preload()
                            ->default(1)
                            ->reactive()
                            ->searchable()
                            ->options(WorkStatusDefination::pluck('name_th', "id"))
                            ->afterStateUpdated(
                                function ($set) {
                                    $set('status_detail_id', null);
                                    $set('positions_id', null);
                                    $set('interview_at', null);
                                }
                            ),
                        Select::make('status_detail_id')
                            ->label('เลือกรายละเอียดสถานะ')
                            ->preload()
                            ->reactive()
                            ->searchable()
                            ->options(
                                fn($get) =>
                                WorkStatusDefinationDetail::where('work_status_def_id', $get('work_status_id'))->pluck('name_th', 'id')
                            )
                            ->afterStateUpdated(
                                fn($state, $set) => $state === 3
                                    ? $set('interview_at', now())
                                    : $set('interview_at', null)
                            ),
                        DateTimePicker::make('interview_at')
                            ->label('ระบุเวลานัดสัมภาณษ์')
                            ->displayFormat('D, j M Y, G:i น.')
                            ->locale('th')
                            ->buddhist()
                            ->minutesStep(5)
                            ->visible(fn($get) => $get('status_detail_id') === 3 ? 1 : 0)
                            ->seconds(false),
                        Select::make('positions_id')
                            ->label('เลือกตำแหน่งที่สมัคร')
                            ->preload()
                            ->visible(fn($get) => $get('work_status_id') === 1 ? 1 : 0)
                            ->reactive()
                            ->multiple()
                            ->options(function () {
                                $lowest_level = OrganizationStructure::getLevelLowest(); //ระดับต่ำสุดของโครงสร้างองค์กร
                                $level_id = organizationStructure::getLevelId($lowest_level); //id ของระดับต่ำสุดของโครงสร้างองค์กร มักเป็น "ตำแหน่งพนักงาน"
                                $org_name = OrganizationStructure::where('organization_level_id', $level_id)->pluck('name_th', 'id');
                                return $org_name;
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['work_status_id'],
                                function ($query) use ($data) {
                                    $query->whereRelation('userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail.workStatusDefDetailBelongsToWorkStatusDef', 'id', $data['work_status_id']);
                                }
                            )
                            ->when(
                                $data['status_detail_id'],
                                function ($query) use ($data) {
                                    $query->whereRelation('userHasoneWorkStatus.workStatusBelongToWorkStatusDefDetail', 'id', $data['status_detail_id']);
                                }
                            )
                            ->when(
                                $data['interview_at'],
                                function (Builder $query, $value) {
                                    $carbonValue = Carbon::parse($value);
                                    $date = $carbonValue->toDateString();   // YYYY-MM-DD
                                    $time = $carbonValue->format('H:i');   // HH:MM

                                    $query->whereHas('userHasoneWorkStatus.workStatusHasonePreEmp', function (Builder $q) use ($date, $time) {
                                        $q->whereDate('interview_at', $date)
                                            ->whereTime('interview_at', '<=', $time);
                                    });
                                }
                            )
                            ->when(
                                $data['positions_id'],
                                function ($query) use ($data) {
                                    $query->whereHas(
                                        'userHasoneResume.resumeHasoneJobPreference',
                                        function ($q) use ($data) {
                                            $q->where(function ($qq) use ($data) {
                                                foreach ($data['positions_id'] as $pos) {
                                                    $qq->orWhereJsonContains('positions_id', (int) $pos);
                                                }
                                            });
                                        }
                                    );
                                }
                            )
                        ;
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
                                ->readOnly(function ($record) {
                                    return filled($record->userHasoneWorkStatus->workStatusHasonePreEmp->interview_at);
                                })
                                ->required()
                                ->validationMessages(['required' => 'กรุณาเลือกวันเวลานัดสัมภาษณ์'])
                                ->native(false)
                                ->placeholder('วันเวลาในการนัดสัมภาษณ์')
                                ->displayFormat('d M Y | เวลา H:i')
                                ->seconds(false)
                                ->locale('th')
                                ->buddhist(),
                            Radio::make('interview_channel')
                                ->disabled(function ($record) {
                                    return filled($record->userHasoneWorkStatus->workStatusHasonePreEmp->interview_at);
                                })
                                ->required()
                                ->validationMessages(['required' => 'กรุณาเลือกช่องทางการสัมภาษณ์'])
                                ->label('ช่องทางการสัมภาษณ์')
                                ->inline(1)
                                ->options([
                                    'online' => 'Online',
                                    'onsite' => 'OnSite'
                                ])

                        ])
                        ->action(function ($record, array $data) {
                            $view_notification = 'view_interview_' . Date::now()->timestamp;
                            $workStatus = $record->userHasoneWorkStatus()->first();
                            $dt = Carbon::parse($data['interview_at'])->locale('th');
                            $workStatus->update([
                                'work_status_def_detail_id' => 3,
                            ]);

                            $workStatus->workStatusHasonePreEmp()->update([
                                'interview_channel' => $data['interview_channel'],
                                'interview_at' => $data['interview_at'],
                            ]);
                            $history = $record->userHasoneHistory();
                            $history->update([
                                'data' => [
                                    ...$history->first()->data,
                                    [
                                        'event' => 'interview scheduled',
                                        'description' => "นัดหมายวันนัดสัมภาษณ์ผ่านช่องทาง \"{$data['interview_channel']}\"",
                                        'value' => $data['interview_at'],
                                        'date' => carbon::now()->format('y-m-d h:i:s'),
                                    ]
                                ],
                            ]);
                            Notification::make() //ต้องรัน Queue
                                ->title('แจ้งวันนัดสัมภาษณ์')
                                ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                                    . "<br><br>ทางบริษัทฯ ขอแจ้งนัดหมายวันสัมภาษณ์งานของท่านใน<br>
                                <B>วัน"
                                    . $dt->translatedFormat('D ที่ j M ')
                                    . $dt->year + 543
                                    . "\nเวลา "
                                    . $dt->format(' H:i')
                                    . " น."
                                    . "</B>"
                                    . "<br>ผ่านช่องทาง <B>\"" . ucwords($data['interview_channel']) . " \"</B>"
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
                                    . "ทางบริษัทฯ ขอแจ้งนัดหมายวันสัมภาษณ์งานของท่านใน\n\nวัน "
                                    . $dt->translatedFormat('D ที่ j M ')
                                    . $dt->year + 543
                                    . "\nเวลา "
                                    . $dt->format(' H:i')
                                    . " น.\n"
                                    . "ผ่านช่องทาง \"" . ucwords($data['interview_channel']) . " \"\n\n"
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
                                    $dt = Carbon::parse($interview_date)->locale('th');
                                    $workStatus->update([
                                        'work_status_def_detail_id' => 2,
                                    ]);
                                    $workStatus->workStatusHasonePreEmp()->update([
                                        'interview_channel' => null,
                                        'interview_at' => null,
                                    ]);

                                    $history = $record->userHasoneHistory();
                                    $history->update([
                                        'data' => [
                                            ...$history->first()->data,
                                            [
                                                'event' => 'cancel interview',
                                                'description' => "ยกเลิกการนัดสัมภาษณ์ของ<br>วัน"
                                                    . $dt->translatedFormat('D ที่ j M ')
                                                    . ($dt->year + 543)
                                                    . " เวลา "
                                                    . $dt->format(' H:i')
                                                    . " น.",
                                                'date' => carbon::now()->format('y-m-d h:i:s'),
                                            ]
                                        ],
                                    ]);
                                    Notification::make() //ต้องรัน Queue
                                        ->title('แจ้งวันนัดสัมภาษณ์')
                                        ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                                            . "<br><br>ทางบริษัทฯ ขอแจ้ง<br>❌ ยกเลิกนัดหมายวันสัมภาษณ์งานของท่านใน<br>
                                            <B>วัน"
                                            . $dt->translatedFormat('D ที่ j M ')
                                            . ($dt->year + 543)
                                            . " เวลา "
                                            . $dt->format(' H:i')
                                            . " น."
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
                                            \n❌ ยกเลิกนัดหมายวันสัมภาษณ์งานของท่านใน\n\nวัน "
                                            . $dt->translatedFormat('D ที่ j M ')
                                            . ($dt->year + 543)
                                            . "\nเวลา "
                                            . $dt->format(' H:i')
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
                    Action::make('history')
                        ->mountUsing(function (Schema $form, $record) {
                            $form->fill($record->userHasoneHistory->attributesToArray());
                        })
                        ->modalCancelActionLabel('ปิด')
                        ->modalSubmitAction(false)
                        ->modalHeading('ประวัติเหตุการณ์')
                        ->icon('heroicon-m-newspaper')
                        ->color(Color::Indigo)
                        ->closeModalByClickingAway(false)
                        ->schema([
                            RepeatableEntry::make('userHasoneHistory.data')
                                ->hiddenLabel()
                                ->table(fn() => self::$isMobile ? null : [
                                    TableColumn::make('เหตุการณ์')->alignment(Alignment::Center)
                                        ->wrapHeader(),
                                    TableColumn::make('Value')->alignment(Alignment::Center)
                                        ->wrapHeader(),
                                    TableColumn::make('เวลาที่เกิดเหตุการณ์')->alignment(Alignment::Center)
                                        ->wrapHeader(),
                                    TableColumn::make('รายละเอียด')->alignment(Alignment::Center)
                                        ->wrapHeader(),
                                ])
                                ->schema([
                                    TextEntry::make('event')
                                        ->label('เหตุการณ์')
                                        ->alignment(fn() => self::$isMobile ? Alignment::Start : Alignment::Center)
                                        ->formatStateUsing(fn($state) => ucwords($state)),
                                    TextEntry::make('value')
                                        ->label('value')
                                        ->formatStateUsing(function ($state) {
                                            if (! is_string($state)) {
                                                return $state;
                                            }
                                            if (! self::isDateTime($state)) {
                                                return $state;
                                            }
                                            $dt = Carbon::parse($state)->locale('th');
                                            return $dt->translatedFormat('D, j M ') . ($dt->year + 543) . ', ' . $dt->format('H:i');
                                        })

                                        ->visible(fn($state) => filled($state))
                                        ->alignment(fn() => self::$isMobile ? Alignment::Start : Alignment::Center),
                                    TextEntry::make('date')
                                        ->label('เวลา')
                                        ->formatStateUsing(function ($state) {
                                            $dt = Carbon::parse($state)->locale('th');
                                            return $dt->translatedFormat('j M ') . ($dt->year + 543) . ', ' . $dt->format('H:i:s');
                                        })
                                        ->alignment(fn() => self::$isMobile ? Alignment::Start : Alignment::Center)
                                        ->columnSpan(fn($get) => $get('value') ? ['default' => 1] : ['default' => 2]),
                                    TextEntry::make('description')
                                        ->html()
                                        ->label('รายละเอียด')
                                        ->alignment(fn() => self::$isMobile ? Alignment::Start : Alignment::Center)
                                        ->columnSpan([
                                            'default' => 3
                                        ]),

                                ])
                                ->columns([
                                    'default' => 3
                                ])

                        ]),

                    DeleteAction::make()->label('ลบพนักงาน'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('ลบข้อมูลของบุคคลที่เลือก')
                        ->visible(fn($model) => (new $model())->isSuperAdmin()),
                    BulkAction::make('mark_as_interviewed')
                        ->icon(Heroicon::ChatBubbleLeftRight)
                        ->visible(
                            fn($livewire) =>
                            $livewire->tableFilters['filter_component']['status_detail_id'] === '3'
                                ? 1 : 0
                        )
                        ->label('มาสัมภาษณ์แล้ว')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->userHasoneWorkStatus->update([
                                    'work_status_def_detail_id' => 4,
                                ]);
                                $history = $record->userHasoneHistory();
                                $history->update([
                                    'data' => [
                                        ...$history->first()->data ?? [],
                                        [
                                            'event' => 'interviewed',
                                            'description' => 'มาสัมภาษณ์แล้ว',
                                            'date' => Carbon::now()->format('Y-m-d h:i:s'),
                                        ]
                                    ],
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                    BulkAction::make('mark_as_waiting_approval')
                        ->icon(Heroicon::CheckCircle)
                        ->label('รออนุมัติจ้างงาน')
                        ->visible(fn($livewire) => in_array(
                            (int) $livewire->tableFilters['filter_component']['status_detail_id'],
                            [4, 7, 8, 9],
                        )  ? 1 : 0)
                        ->action(function ($records, $action) {
                            foreach ($records as $record) {
                                $work_status = $record->userHasoneWorkStatus;
                                $work_status->update([
                                    'work_status_def_detail_id' => 6,
                                ]);
                                $work_status->workStatusHasonePreEmp()->update([
                                    'result_at' => now(),
                                ]);
                                $history = $record->userHasoneHistory();
                                $history->update([
                                    'data' => [
                                        ...$history->first()->data ?? [],
                                        [
                                            'event' => 'selection results',
                                            'value' => 'waiting approval',
                                            'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "รออนุมัติจ้างงาน"',
                                            'date' => Carbon::now()->format('Y-m-d h:i:s'),
                                        ]
                                    ],
                                ]);
                                notification::make('noti')
                                    ->title(function () use ($action) {
                                        return 'กำหนดสถานะเป็น "' . $action->getLabel() . '" เรียบร้อยแล้ว';
                                    })
                                    ->success()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                    BulkAction::make('mark_as_rejected')
                        ->icon(Heroicon::XCircle)
                        ->label('ไม่ผ่านการคัดเลือก')
                        ->visible(
                            fn($livewire) => in_array((int) $livewire->tableFilters['filter_component']['status_detail_id'], [4, 6, 8, 9])
                                ? 1 : 0
                        )
                        ->action(function ($records, $action) {
                            foreach ($records as $record) {
                                $work_status = $record->userHasoneWorkStatus;
                                $work_status->update([
                                    'work_status_def_detail_id' => 7,
                                ]);
                                $work_status->workStatusHasonePreEmp()->update([
                                    'result_at' => now(),
                                ]);

                                $history = $record->userHasoneHistory();
                                $history->update([
                                    'data' => [
                                        ...$history->first()->data ?? [],
                                        [
                                            'event' => 'selection results',
                                            'value' => 'rejected',
                                            'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "ไม่ผ่านการคัดเลือก"',
                                            'date' => Carbon::now()->format('Y-m-d h:i:s'),
                                        ]
                                    ],
                                ]);
                                notification::make('noti')
                                    ->title(function () use ($action) {
                                        return 'กำหนดสถานะเป็น "' . $action->getlabel() . '" เรียบร้อยแล้ว';
                                    })
                                    ->success()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->color('danger'),
                    BulkAction::make('mark_as_waiting_compare')
                        ->icon(Heroicon::Users)
                        ->label('รอเปรียบเทียบ')
                        ->visible(
                            fn($livewire) => in_array((int) $livewire->tableFilters['filter_component']['status_detail_id'], [4, 6, 7, 9])
                                ? 1 : 0
                        )
                        ->action(function ($records, $action) {
                            foreach ($records as $record) {
                                $work_status = $record->userHasoneWorkStatus;
                                $work_status->update([
                                    'work_status_def_detail_id' => 8,
                                ]);
                                $work_status->workStatusHasonePreEmp()->update([
                                    'result_at' => now(),
                                ]);
                                $history = $record->userHasoneHistory();
                                $history->update([
                                    'data' => [
                                        ...$history->first()->data ?? [],
                                        [
                                            'event' => 'selection results',
                                            'value' => 'waiting compare',
                                            'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "รอเปรียบเทียบ"',
                                            'date' => Carbon::now()->format('Y-m-d h:i:s'),
                                        ]
                                    ],
                                ]);
                                notification::make('noti')
                                    ->title(function () use ($action) {
                                        return 'กำหนดสถานะเป็น "' . $action->getlabel() . '" เรียบร้อยแล้ว';
                                    })
                                    ->success()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->color('warning'),
                    BulkAction::make('mark_as_substitute')
                        ->icon(Heroicon::ArrowPathRoundedSquare)
                        ->label('สำรอง')
                        ->visible(
                            fn($livewire) => in_array((int) $livewire->tableFilters['filter_component']['status_detail_id'], [4, 6, 8, 7])
                                ? 1 : 0
                        )
                        ->action(function ($records, $action) {
                            foreach ($records as $record) {
                                $work_status = $record->userHasoneWorkStatus;
                                $work_status->update([
                                    'work_status_def_detail_id' => 9,
                                ]);
                                $work_status->workStatusHasonePreEmp()->update([
                                    'result_at' => now(),
                                ]);
                                $history = $record->userHasoneHistory();
                                $history->update([
                                    'data' => [
                                        ...$history->first()->data ?? [],
                                        [
                                            'event' => 'selection results',
                                            'value' => 'substitute',
                                            'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "สำรอง"',
                                            'date' => Carbon::now()->format('Y-m-d h:i:s'),
                                        ]
                                    ],
                                ]);
                                notification::make('noti')
                                    ->title(function () use ($action) {
                                        return 'กำหนดสถานะเป็น "' . $action->getlabel() . '" เรียบร้อยแล้ว';
                                    })
                                    ->success()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->color(Color::Blue),
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
            Stack::make([
                TextColumn::make('userHasoneWorkStatus.workStatusHasonePreEmp.interview_at')
                    ->sortable()
                    ->label('วันนัดสัมภาษณ์')
                    ->buddhistDate('D, j M Y, G:i น.')
                    ->prefix(new HtmlString('<div><B>วันที่นัดสัมภาษณ์ : </B></div>')),
                TextColumn::make('userHasoneWorkStatus.workStatusHasonePreEmp.interview_channel')
                    ->formatStateUsing(fn($state) => ucwords($state))
            ])->visible(fn($record) => $record?->isPreEmployment()),
            Stack::make([
                TextColumn::make('label_job_preferrnet')
                    ->default('ตำแหน่งที่สมัคร :')
                    ->weight(FontWeight::Bold),
                TextColumn::make('userHasoneResume.resumeHasoneJobPreference.positions_id')
                    ->label('ตำแหน่งที่สมัคร')
                    ->formatStateUsing(function ($state) {
                        $lowest_level = OrganizationStructure::getLevelLowest(); //ระดับต่ำสุดของโครงสร้างองค์กร
                        $level_id = organizationStructure::getLevelId($lowest_level); //id ของระดับต่ำสุดของโครงสร้างองค์กร มักเป็น "ตำแหน่งพนักงาน"
                        $org_level = OrganizationStructure::where('organization_level_id', $level_id)->get();
                        return $org_level->where('id', $state)->first()->name_th;
                    })
            ])->visible(fn($record) => $record?->isPreEmployment()),

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

    protected static function isDateTime($value): bool
    {
        try {
            Carbon::parse($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
