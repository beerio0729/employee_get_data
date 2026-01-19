<?php

namespace App\Filament\Panel\Admin\Resources\Users\Tables;

use Carbon\Carbon;
use App\Models\Role;
use Filament\Tables\Table;
use App\Jobs\BulkInterview;
use Detection\MobileDetect;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\Document\Idcard;
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
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use App\Services\GoogleCalendarService;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use App\Models\WorkStatus\PostEmployment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use App\Models\WorkStatus\PostEmploymentGrade;
use App\Services\PreEmployment\ApprovedService;
use App\Services\PreEmployment\InterviewService;
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
                    Action::make('employment_contract')
                        ->label('ทำสัญญาจ้างงาน')
                        ->mountUsing(function (Schema $form, $record) {
                            $form->fill($record->userHasoneWorkStatus->workStatusHasonePostEmp->attributesToArray());
                        })
                        ->icon('heroicon-m-document-text')
                        ->color(Color::Stone)
                        ->modalHeading('ข้อมูลการจ้างงาน')
                        ->modalWidth(Width::FiveExtraLarge)
                        ->closeModalByClickingAway(false)
                        ->schema([
                            Fieldset::make('employment_contract_form')
                                ->columnSpanFull()
                                ->hiddenLabel()
                                ->columns(3)
                                ->contained(false)
                                ->schema([
                                    TextInput::make('employee_code')
                                        ->label('รหัสพนักงาน')
                                        ->default(function () {
                                            $hiredAt =  PostEmployment::latest()->first()->hired_at;
                                            if (! $hiredAt) {
                                                return null;
                                            }
                                            $year = Carbon::parse($hiredAt)->year;
                                            $count = PostEmployment::whereYear('hired_at', $year)->count() + 1;
                                            return sprintf('%d/%03d', $year, $count);
                                        }),

                                    Select::make('lowest_org_structure_id')
                                        ->label('เลือกตำแหน่งที่สมัคร')
                                        ->options(function ($record) {
                                            $position = [];
                                            $positions_id = $record->userHasoneResumeToJobPreference->positions_id;
                                            foreach ($positions_id as $position_id) {
                                                $position[$position_id] = OrganizationStructure::where('id', $position_id)->first()->name_th;
                                            }
                                            return $position;
                                        })
                                        ->preload(),

                                    Select::make('post_employment_grade_id')
                                        ->label('ระดับพนักงาน')
                                        ->options(
                                            PostEmploymentGrade::query()
                                                ->get()
                                                ->mapWithKeys(fn($row) => [
                                                    $row->id => "{$row->name_th} (G{$row->grade})",
                                                ])
                                                ->toArray()
                                        ),
                                    TextInput::make('salary')
                                        ->label('เงินเดือน')
                                        ->suffix('บาท'),
                                    DatePicker::make('hired_at')
                                        ->label('วันที่เริ่มงาน')
                                        ->minDate(now())
                                        ->required()
                                        ->validationMessages(['required' => 'กรุณาเลือกวันนัดสัมภาษณ์'])
                                        ->native(false)
                                        ->placeholder('DD MM YYYY')
                                        ->displayFormat('d M Y')
                                        ->seconds(false)
                                        ->prefix('วันที่')
                                        ->locale('th')
                                        ->buddhist(),
                                    Select::make('manager_id')
                                        ->columnSpan(1)
                                        ->label('หัวหน้า')
                                        ->options(function ($get) {
                                            $org = OrganizationStructure::where('id', $get('lowest_org_structure_id'));
                                            $parent_id = $org->first()->parent_id;
                                            $idCard = Idcard::query()->whereRelation('idcardBelongtoUser.userHasoneWorkStatus.workStatusHasonePostEmp.postEmpBelongToOrg', 'parent_id', $parent_id)
                                                ->whereRelation('idcardBelongtoUser.userHasoneWorkStatus.workStatusHasonePostEmp', 'post_employment_grade_id', 7)
                                                ->get()
                                                ->mapWithKeys(fn($emp) => [
                                                    $emp->idcardBelongtoUser->id =>
                                                    "{$emp->prefix_name_th} {$emp->name_th} {$emp->last_name_th} 
                                                    ({$emp->idcardBelongtoUser->userHasoneWorkStatus->workStatusHasonePostEmp->postEmpBelongToOrg->name_th})",
                                                ]);

                                            return $idCard;
                                        }),
                                ])
                        ])
                        ->action(function ($data, $record) {
                            $approved = new ApprovedService();
                            $approved->create($record, $data);
                            
                            Notification::make()
                                ->title("อนุมัติการจ้างงานคุณ \"{$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th}\" เรียบร้อยแล้ว")
                                ->success()
                                ->send();
                        }),
                    Action::make('interview')
                        ->label('นัดหมายวันสัมภาษณ์')
                        ->mountUsing(function (Schema $form, $record) {
                            $form->fill($record->userHasoneWorkStatus->workStatusHasonePreEmp->attributesToArray());
                        })
                        ->hidden(
                            function ($record) {
                                $work_state_detail = $record->userHasoneWorkStatus?->workStatusBelongToWorkStatusDefDetail;
                                return in_array($work_state_detail->work_phase, ["pre_interview_time", "post_result_time"]);
                            }
                        )
                        ->color('success')
                        ->icon('heroicon-m-calendar')
                        ->modalSubmitActionLabel(
                            fn($record) =>
                            filled($record?->userHasoneWorkStatus?->workStatusHasonePreEmp->interview_at)
                                ? 'แก้ไขนัดสัมภาษณ์'
                                : 'นัดสัมภาษณ์'
                        )
                        ->modalSubmitAction(function ($action, $record) {
                            $action->disabled(
                                function () use ($record) {
                                    $work_state_detail = $record->userHasoneWorkStatus?->workStatusBelongToWorkStatusDefDetail;
                                    return $work_state_detail->work_phase === "pre_interview_time";
                                }
                            );
                        })
                        ->modalHeading('นัดหมายวันสัมภาษณ์')
                        ->modalWidth(Width::ExtraLarge)
                        ->closeModalByClickingAway(false)
                        ->schema([
                            Fieldset::make('Label')
                                ->contained(false)
                                ->hiddenLabel()
                                ->columns(3)
                                ->schema([
                                    DatePicker::make('interview_day')
                                        ->hiddenLabel()
                                        ->minDate(now())
                                        ->required()
                                        ->validationMessages(['required' => 'กรุณาเลือกวันนัดสัมภาษณ์'])
                                        ->native(false)
                                        ->placeholder('DD MM YYYY')
                                        ->displayFormat('d M Y')
                                        ->seconds(false)
                                        ->prefix('วันที่')
                                        ->locale('th')
                                        ->buddhist()
                                        ->columnSpan(2),
                                    TimePicker::make('interview_time')
                                        ->hiddenLabel()
                                        ->prefix('เวลา')
                                        ->suffix('น.')
                                        ->native(false)
                                        ->required()
                                        ->seconds(false)
                                        ->validationMessages(['required' => 'กรุณาเลือกเวลานัดสัมภาษณ์'])
                                        ->placeholder('hh:mm')
                                        ->minutesStep(5)
                                        ->columns(1),
                                    Select::make('interview_duration')
                                        ->label('ระยะเวลาการสัมภาษณ์')
                                        ->required()
                                        ->columnSpanFull()
                                        ->validationMessages(['required' => 'กรุณาระบุระยะเวลาในการสัมภาษณ์'])
                                        ->options([
                                            15 => '15 นาที',
                                            30 => '30 นาที',
                                            45 => '45 นาที',
                                            60 => '1 ชั่วโมง',
                                            75 => '1 ชั่วโมง 15 นาที',
                                            90 => '1 ชั่วโมง 30 นาที',
                                            105 => '1 ชั่วโมง 45 นาที',
                                            120 => '2 ชั่วโมง',
                                        ]),
                                    Radio::make('interview_channel')
                                        ->required()
                                        ->columnSpanFull()
                                        ->validationMessages(['required' => 'กรุณาเลือกช่องทางการสัมภาษณ์'])
                                        ->label('ช่องทางการสัมภาษณ์')
                                        ->inline(1)
                                        ->options([
                                            'online' => 'Online',
                                            'onsite' => 'OnSite'
                                        ]),

                                ])
                        ])
                        ->action(function ($record, array $data) {
                            $data['interview_at'] = "{$data['interview_day']} {$data['interview_time']}";

                            $hasInterview = filled($record?->userHasoneWorkStatus?->workStatusHasonePreEmp->interview_at);
                            $interviewService = new InterviewService();
                            if ($hasInterview) {
                                $interviewService->update($record, $data);
                            } else {
                                $interviewService->create($record, $data);
                            }
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
                                    $interviewService = new InterviewService();
                                    $interviewService->delete($record);
                                })
                                ->cancelParentActions()
                                ->successNotificationTitle('เคลียร์ข้อมูลทั้งหมดเรียบร้อยแล้ว'),
                            Action::make('see_calendar')
                                ->label(function ($record) {
                                    $calendar_id = $record?->userHasoneWorkStatus?->workStatusHasonePreEmp?->google_calendar_id;
                                    $calendar = new GoogleCalendarService();
                                    $calendar_data = $calendar->getEvent($calendar_id);

                                    if (blank($calendar_data->hangoutLink)) {
                                        return "สร้างลิงค์ห้องประชุม";
                                    } else {
                                        return "เข้าห้องประชุม";
                                    }
                                })
                                ->color(Color::Indigo)
                                ->visible(function ($record) {

                                    $preEmp = $record?->userHasoneWorkStatus?->workStatusHasonePreEmp;
                                    $workPhase = $record?->userHasoneWorkStatus?->workStatusBelongToWorkStatusDefDetail?->work_phase;

                                    // ถ้าไม่มี Google Calendar ID ปิดเลย
                                    if (!filled($preEmp?->google_calendar_id)) {
                                        return false;
                                    }

                                    // ถ้า phase ไม่ตรง ปิดเลย
                                    if ($workPhase !== 'interview_scheduled_time') {
                                        return false;
                                    }

                                    // เวลาเปิด/ปิด (ตอนนี้ + 10 นาที buffer)
                                    $interviewAt = Carbon::parse($preEmp?->interview_at)->startOfMinute()->addMinute(10);

                                    // ปุ่ม visible ถ้าเวลาปัจจุบันยังไม่เกินเวลา + buffer
                                    return now()->startOfMinute()->lte($interviewAt);
                                })
                                ->url(function ($record) {
                                    $calendar_id = $record?->userHasoneWorkStatus?->workStatusHasonePreEmp?->google_calendar_id;
                                    $calendar = new GoogleCalendarService();
                                    $calendar_data = $calendar->getEvent($calendar_id);

                                    if (blank($calendar_data->hangoutLink)) {
                                        parse_str(parse_url($calendar_data->htmlLink, PHP_URL_QUERY), $query);
                                        $id = $query['eid'];

                                        return "https://calendar.google.com/calendar/u/0/r/eventedit/{$id}";
                                    } else {
                                        return $calendar_data->hangoutLink;
                                    }
                                })
                                ->action(fn($livewire) => $livewire->dispatch('closeActionModal'))
                                ->cancelParentActions()
                                ->openUrlInNewTab()
                        ]),
                    Action::make('role_id')
                        ->mountUsing(function (Schema $form, $record) {
                            $form->fill($record->attributesToArray());
                        })
                        //->disabled(fn($record) => !$record->userHasManyPostEmp()->exists()) // รอจัดการเรื่องพนักงาน
                        ->visible(fn() => auth()->user()->role_id === 1)
                        ->color(fn($record) => $record->role_id === 3 ? 'info' : 'gray')
                        ->label('สิทธิ์การเข้าถึง')
                        ->icon(Heroicon::Key)
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
                                            $interview_date = Carbon::parse($state)->locale('th');
                                            return $interview_date->translatedFormat('D, j M ') . ($interview_date->year + 543) . ', ' . $interview_date->format('H:i');
                                        })

                                        ->visible(fn($state) => filled($state))
                                        ->alignment(fn() => self::$isMobile ? Alignment::Start : Alignment::Center),
                                    TextEntry::make('date')
                                        ->label('เวลา')
                                        ->formatStateUsing(function ($state) {
                                            $interview_date = Carbon::parse($state)->locale('th');
                                            return $interview_date->translatedFormat('j M ') . ($interview_date->year + 543) . ', ' . $interview_date->format('H:i:s');
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
                ])->icon(Heroicon::AdjustmentsVertical)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('ลบข้อมูลของบุคคลที่เลือก')->requiresConfirmation()
                        ->visible(fn($model) => (new $model())->isSuperAdmin()),
                    BulkAction::make('interviewe')
                        ->label('นัดสัมภาษณ์')
                        ->visible(
                            fn($livewire) =>
                            $livewire->tableFilters['filter_component']['status_detail_id'] === '2'
                                ? 1 : 0
                        )
                        ->color('success')
                        ->icon('heroicon-m-calendar')
                        ->schema([
                            Repeater::make('multiform_interview')
                                ->hiddenLabel()
                                ->columns(2)
                                ->reorderable(false)
                                ->deletable(false)
                                ->addable(false)
                                ->schema([
                                    TextInput::make('employee_name')
                                        ->label('ชื่อพนักงาน')
                                        ->readOnly(),
                                    DateTimePicker::make('interview_at')
                                        ->label('วัน-เวลานัดสัมภาษณ์')
                                        ->required()
                                        ->validationMessages(['required' => 'กรุณาเลือกวันเวลานัดสัมภาษณ์'])
                                        ->native(false)
                                        ->placeholder('วันเวลาในการนัดสัมภาษณ์')
                                        ->displayFormat('d M Y | เวลา H:i')
                                        ->seconds(false)
                                        ->locale('th')
                                        ->buddhist(),
                                    Select::make('interview_duration')
                                        ->label('ระยะเวลาการสัมภาษณ์')
                                        ->required()
                                        ->validationMessages(['required' => 'กรุณาระบุระยะเวลาในการสัมภาษณ์'])
                                        ->options([
                                            15 => '15 นาที',
                                            30 => '30 นาที',
                                            45 => '45 นาที',
                                            60 => '1 ชั่วโมง',
                                            75 => '1 ชั่วโมง 15 นาที',
                                            90 => '1 ชั่วโมง 30 นาที',
                                            105 => '1 ชั่วโมง 45 นาที',
                                            120 => '2 ชั่วโมง',
                                        ]),
                                    Radio::make('interview_channel')
                                        ->required()
                                        ->validationMessages(['required' => 'กรุณาเลือกช่องทางการสัมภาษณ์'])
                                        ->label('ช่องทางการสัมภาษณ์')
                                        ->inline(1)
                                        ->options([
                                            'online' => 'Online',
                                            'onsite' => 'OnSite'
                                        ]),
                                ])

                        ])
                        ->fillForm(fn($records): array => [
                            'multiform_interview' => self::bulkInterview($records)
                        ])
                        ->action(function ($records, array $data) {
                            BulkInterview::dispatch(records: $records, data: $data, action: 'create');
                            Notification::make()
                                ->title('นัดหมายวันสัมภาษณ์เรียบร้อยแล้ว')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('cancel_interviewe')
                        ->label('ยกเลิกนัดสัมภาษณ์')
                        ->visible(
                            fn($livewire) =>
                            $livewire->tableFilters['filter_component']['status_detail_id'] === '3'
                                ? 1 : 0
                        )
                        ->requiresConfirmation()
                        ->color('danger')
                        ->label('ยกเลิกนัดสัมภาษณ์')
                        ->modalHeading("ยกเลิกนัดสัมภาษณ์")
                        ->modalDescription("คุณต้องการยกเลิกนัดสัมภาษณ์ใช่หรือไม่")
                        ->modalSubmitActionLabel('ยืนยัน')
                        ->icon('heroicon-m-x-circle')
                        ->action(function ($records) {
                            BulkInterview::dispatch(records: $records, action: 'delete');
                            Notification::make()
                                ->title('ยกเลิกนัดสัมภาษณ์เรียบร้อยแล้ว')
                                ->success()
                                ->send();
                        }),
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
                                $history->updateOrCreate(
                                    ['user_id' => $record->id],
                                    [
                                        'data' => [
                                            ...$history->first()->data ?? [],
                                            [
                                                'event' => 'interviewed',
                                                'description' => 'มาสัมภาษณ์แล้ว',
                                                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                                            ]
                                        ],
                                    ]
                                );
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
                                $history->updateOrCreate(
                                    ['user_id' => $record->id],
                                    [
                                        'data' => [
                                            ...$history->first()->data ?? [],
                                            [
                                                'event' => 'selection results',
                                                'value' => 'waiting approval',
                                                'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "รออนุมัติจ้างงาน"',
                                                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                                            ]
                                        ],
                                    ]
                                );
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
                                $history->updateOrCreate(
                                    ['user_id' => $record->id],
                                    [
                                        'data' => [
                                            ...$history->first()->data ?? [],
                                            [
                                                'event' => 'selection results',
                                                'value' => 'rejected',
                                                'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "ไม่ผ่านการคัดเลือก"',
                                                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                                            ]
                                        ],
                                    ]
                                );
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
                                $history->updateOrCreate(
                                    ['user_id' => $record->id],
                                    [
                                        'data' => [
                                            ...$history->first()->data ?? [],
                                            [
                                                'event' => 'selection results',
                                                'value' => 'waiting compare',
                                                'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "รอเปรียบเทียบ"',
                                                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                                            ]
                                        ],
                                    ]
                                );
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
                                $history->updateOrCreate(
                                    ['user_id' => $record->id],
                                    [
                                        'data' => [
                                            ...$history->first()->data ?? [],
                                            [
                                                'event' => 'selection results',
                                                'value' => 'substitute',
                                                'description' => 'ผลการคัดเลือกหลังสัมภาษณ์ คือ "สำรอง"',
                                                'date' => Carbon::now()->format('Y-m-d H:i:s'),
                                            ]
                                        ],
                                    ]
                                );
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

    protected static function panelDetailUserForPhone(): array
    {
        return self::$isMobile ? self::panelDetailUserComponent() : [];
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

    protected static function bulkInterview($records): array
    {
        $fill = [];
        foreach ($records as $record) {
            $id_card = $record->userHasoneIdcard;
            $fill[] = [
                "employee_name" => "{$id_card->name_th} {$id_card->last_name_th}"
            ];
        }

        return $fill;
    }
}

                                    // TextInput::make('employee_code')
                                    //     ->label('รหัสพนักงาน')
                                    //     ->default(function () {
                                    //         $hiredAt =  PostEmployment::latest()->first()->hired_at;
                                    //         if (! $hiredAt) {
                                    //             return null;
                                    //         }
                                    //         $year = Carbon::parse($hiredAt)->year;
                                    //         $count = PostEmployment::whereYear('hired_at', $year)->count() + 1;
                                    //         return sprintf('%d/%03d', $year, $count);
                                    //     })
                                    //     ->readOnly(),