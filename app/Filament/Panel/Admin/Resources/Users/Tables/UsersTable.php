<?php

namespace App\Filament\Panel\Admin\Resources\Users\Tables;

use Dom\Text;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Employee;
use Filament\Tables\Table;
use Detection\MobileDetect;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Overrides\Filament\Schemas\Components\Tab;
use App\Services\LineSendMessageService;
use Termwind\Components\Li;

class UsersTable
{
    public static bool $isMobile;
    public static bool $isAndroidOS;

    public static function configure(Table $table): Table
    {
        $isEmployee = fn($record) => in_array($record->role_id, [1, 2, 3]);
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
                        ->state((function ($component, $record) {
                            $doc = $record->userHasmanyDocEmp()->where('file_name', 'image_profile')->first();
                            return $doc ? $doc->path : asset('storage/user.png');
                        }))
                        ->extraAttributes(['style' => 'width:50px']),
                    Stack::make([
                        TextColumn::make('full_name')->label('ชื่อภาษาไทย')->searchable()->sortable()
                            ->state(function ($record) {
                                $user = $record->userHasoneIdcard;
                                if (!empty($user->name_th)) {
                                    return "{$user->prefix_name_th} {$user->name_th} {$user->last_name_th}";
                                } else {
                                    return 'ไม่มีข้อมูลชื่อภาษาไทย';
                                }
                            }),
                        TextColumn::make('full_name_en')->label('ชื่อ')->searchable()->sortable()
                            ->state(function ($record) {
                                $user = $record->userHasoneIdcard;
                                if (!empty($user->name_en)) {
                                    return "{$user->prefix_name_en} {$user->name_en} {$user->last_name_en}";
                                } else {
                                    return 'ไม่มีข้อมูลชื่อภาษาอังกฤษ';
                                }
                            }),

                    ]),
                    ...self::detailUserSelectDevice(),
                ]),
                ...self::panelDetailUserSelectDevice(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('interview_date')
                    ->hiddenLabel()
                    ->mountUsing(function (Schema $form, $record) {
                        $form->fill($record->attributesToArray());
                    })
                    ->disabled(fn($record) => $record->role_id !== 4)
                    ->color(fn($record) => $record->role_id === 4 ? 'info' : 'gray')
                    ->tooltip(fn($record) => $record->role_id === 4 ? 'นัดหมายวันสัมพาษก์ผู้สมัครงาน' : null)
                    ->icon('heroicon-m-calendar')
                    ->modalSubmitActionLabel('อับเดตข้อมูล')
                    ->modalHeading('นัดหมายวันสัมภาษณ์ผู้สมัครงาน')
                    ->modalWidth(Width::Medium)
                    ->closeModalByClickingAway(false)
                    ->schema([
                        DateTimePicker::make('interview_date')
                            ->hiddenLabel()
                            ->required()
                            ->validationMessages(['required' => 'กรุณาเลือกวันเวลานัดสัมภาษณ์'])
                            ->native(false)
                            ->placeholder('วันเวลาในการนัดสัมภาษณ์')
                            ->displayFormat('d M Y H:i')
                            ->seconds(false)
                            ->locale('th')
                            ->buddhist(),
                    ])
                    ->action(function ($record, array $data) {
                        $view_notification = 'view_interview_' . Date::now()->timestamp;
                        $record->update([
                            'interview_date' => $data['interview_date'],
                        ]);
                        Notification::make()
                            ->title('แจ้งวันนัดสัมภาษณ์')
                            ->body("เรียน คุณ {$record->userHasoneIdcard->name_th} {$record->userHasoneIdcard->last_name_th} \n\n"
                                . "ทางบริษัทฯ ขอแจ้งนัดหมายวันสัมภาษณ์งานของท่าน\n\nในวันที่
                                <B>"
                                . Carbon::parse($data['interview_date'])->locale('th')->translatedFormat('d M ')
                                . (Carbon::parse($data['interview_date'])->year + 543)
                                . "\nเวลา "
                                . Carbon::parse($data['interview_date'])->format(' H:i')
                                . " น.\n\n"
                                . "</B>"
                                . "โปรดเตรียมเอกสารที่เกี่ยวข้องและมาถึงก่อนเวลานัดหมาย 10 นาที \n\n"
                                . "ขอบคุณค่ะ")
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
                                . Carbon::parse($data['interview_date'])->locale('th')->translatedFormat('d M ')
                                . (Carbon::parse($data['interview_date'])->year + 543)
                                . "\nเวลา "
                                . Carbon::parse($data['interview_date'])->format(' H:i')
                                . " น.\n\n"
                                . "โปรดเตรียมเอกสารที่เกี่ยวข้องและมาถึงก่อนเวลานัดหมาย 10 นาที \n\n"
                                . "ขอบคุณค่ะ",
                        ]);
                        Notification::make()
                            ->title('นัดหมายวันสัมภาษณ์เรียบร้อยแล้ว')
                            ->success()
                            ->send();
                    }),
                EditAction::make()->tooltip('ดูรายละเอียด')->icon('heroicon-m-eye')->hiddenLabel(),
                DeleteAction::make()->tooltip('ลบพนักงาน')->hiddenLabel(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function detailUser(): array
    {
        return [
            Stack::make([
                TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->iconColor('warning')
                    ->copyable()
                    ->copyMessage('คัดลอกแล้ว')->copyMessageDuration(1500)->searchable()->sortable(),
                TextColumn::make('userHasoneResume.tel')
                    ->icon('heroicon-m-phone')
                    ->iconColor('primary')
                    ->default('ไม่ได้ระบุ')
                    ->url(fn($record) => 'tel:' . $record->userHasoneResume->tel),
            ])->space(1),
            TextColumn::make('interview_date')->buddhistDate('d M Y h:i')
                ->prefix(new HtmlString('<div><strong>วันที่นัดสัมภาษณ์: </strong></div>')),
            SelectColumn::make('role_id')
                ->label('ระดับพนักงาน')
                ->afterStateUpdated(function ($state, $record) {
                    $emp_status = $record->userBelongToRole->name_th;
                    Notification::make()
                        ->title("เปลี่ยนสถานะเป็น \"{$emp_status}\" แล้ว")
                        ->success()
                        ->send();
                    if ($state < 4) {
                        $record->update(['interview_date' => null]);
                    }
                })
                ->options(function ($record) {
                    //dump($record->where('role_id', [1,2])->exists());
                    $user = auth()->user();
                    if ($user->role_id == 1) {
                        // super admin เห็นทุก role
                        return Role::where('active', 1)->pluck('name_th', 'id');
                    }
                    if ($user->role_id === 2) {
                        if (in_array($record->role_id, [1, 2])) {
                            return Role::where('active', 1)->pluck('name_th', 'id');
                        } else {
                            return Role::whereIn('id', [3, 4])->pluck('name_th', 'id');
                        }
                    }
                    return []; // คนอื่นเห็น role ของตัวเอง หรือไม่เห็นเลย
                })
                ->disabled(function ($record) {
                    $user = auth()->user();
                    if ($user->role_id === 2) {
                        if (in_array($record->role_id, [1, 2])) {
                            return 1;
                        }
                    }
                })
                ->placeholder('เลือกระดับพนักงาน')
                ->grow(false),

        ];
    }

    public static function panelDetailUser(): array
    {
        return [
            Panel::make([
                Split::make([
                    ...self::detailUser()
                ])->From('sm')
            ])->collapsed()
        ];
    }

    protected static function detailUserSelectDevice(): array
    {
        return self::$isMobile ? [] : self::detailUser();
    }

    protected static function panelDetailUserSelectDevice()
    {
        if (self::$isMobile) {
            return self::panelDetailUser();
        } else {
            return [];
        }
    }
}
