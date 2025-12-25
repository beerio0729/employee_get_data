<?php

namespace App\Filament\Panel\Admin\Resources\Users\Tables;

use Carbon\Carbon;
use App\Models\Role;
use Filament\Tables\Table;
use Detection\MobileDetect;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Services\LineSendMessageService;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Forms\Components\DateTimePicker;

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
            ->recordUrl(fn() => null) //ป้องกันไม่ให้กดที่ตารางแล้วแก้ไข้
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
                                if (!empty($user->name_th)) {
                                    return "{$user->prefix_name_th} {$state} {$user->last_name_th}";
                                } else {
                                    return 'ไม่มีข้อมูลชื่อภาษาไทย';
                                }
                            }),
                        TextColumn::make('work_status')->label('สถานะ')->searchable()->sortable()
                            ->formatStateUsing(function ($state, $record) {
                                if ($state === 'applicant') {
                                    $status = config("iconf.applicant_status.{$record->userHasoneApplicant?->status}");
                                } else {
                                    $status = config("iconf.employee_status.{$record->userHasoneEmployee?->status}");
                                }
                                return config("iconf.work_status.{$state}") . " : " . $status;
                            }),

                    ]),
                    ...self::detailUserForDesktop(),
                ]),
                ...self::panelDetailUserForPhone(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('interview')
                        ->label('นัดหมายวันสัมภาษณ์')
                        ->mountUsing(function (Schema $form, $record) {
                            $form->fill($record->userHasoneApplicant->attributesToArray());
                        })
                        ->visible(
                            fn($record) => in_array(
                                $record->userHasoneApplicant->status,
                                ['doc_passed', 'interview_scheduled', 'no_interviewed']
                            )
                        )
                        ->color('success')
                        ->icon('heroicon-m-calendar')
                        ->modalSubmitActionLabel('อับเดตข้อมูล')
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
                            $record->userHasoneApplicant()->update([
                                'status' => 'interview_scheduled',
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
                                ->visible(fn($record) => $record->userHasoneApplicant->status === 'interview_scheduled')
                                ->requiresConfirmation()
                                ->color('danger')
                                ->label('ยกเลิกนัดสัมภาษณ์')
                                ->modalHeading("ยกเลิกนัดสัมภาษณ์")
                                ->modalDescription("คุณต้องการยกเลิกนัดสัมภาษณ์ใช่หรือไม่")
                                ->modalSubmitActionLabel('ยืนยัน')
                                ->action(function ($record) {
                                    $view_notification = 'view_interview_' . Date::now()->timestamp;
                                    $applicant = $record->userHasoneApplicant();
                                    $interview_date = $applicant->first()->interview_at;
                                    $applicant->update([
                                        'status' => 'doc_passed',
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
                        //->disabled(fn($record) => !$record->userHasoneEmployee()->exists()) // รอจัดการเรื่องพนักงาน
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

                    EditAction::make()->label('ดูรายละเอียด')->icon('heroicon-m-eye')->color('warning'),
                    DeleteAction::make()->label('ลบพนักงาน'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            TextColumn::make('userHasoneApplicant.interview_at')
                ->searchable()->sortable()
                ->label('วันนัดสัมภาษณ์')
                ->buddhistDate('d M Y H:i')
                ->prefix(new HtmlString('<div><strong>วันที่นัดสัมภาษณ์ :</strong></div>'))
                ->visible(fn($record) => $record?->work_status === 'applicant'),
            Stack::make([
                TextColumn::make('userHasoneEmployee.employeeBelongToDepartment.name')
                    ->searchable()->sortable()
                    ->label('แผนก'),
                TextColumn::make('userHasoneEmployee.employeeBelongToPosition.name')
                    ->searchable()->sortable()
                    ->label('ตำแหน่ง'),
            ])->space(1)->visible(fn($record) => $record?->work_status === 'employee'),
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
