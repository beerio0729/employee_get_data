<?php

namespace App\Filament\Resources\Users\Tables;

use Dom\Text;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Employee;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SelectColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Split::make([
                    TextColumn::make('#')
                        ->alignEnd()
                        ->state(fn($record) => $record->getKey())
                        ->grow(false)
                        ->visibleFrom('sm')
                        ->extraAttributes(['style' => 'width:20px']),
                    ImageColumn::make('userHasoneResume.image')
                        ->disk('public')
                        ->simpleLightbox()
                        ->circular()
                        ->grow(false)
                        ->state((function ($component, $record) {
                            $doc = $record->userHasmanyDocEmp()->where('file_name', 'image_profile')->first();
                            return $doc ? $doc->path : null;
                        }))
                        ->extraAttributes(['style' => 'width:50px']),
                    Stack::make([
                        TextColumn::make('full_name')->label('ชื่อภาษาไทย')->searchable()->sortable()
                            ->state(function ($record) {
                                $user = $record->userHasoneIdcard;
                                if (!empty($user)) {
                                    return "{$user->prefix_name_th} {$user->name_th} {$user->last_name_th}";
                                } else {
                                    return 'ไม่มีข้อมูลชื่อภาษาไทย';
                                }
                            }),
                        TextColumn::make('full_name_en')->label('ชื่อ')->searchable()->sortable()
                            ->state(function ($record) {
                                $user = $record->userHasoneIdcard;
                                if (!empty($user)) {
                                    return "{$user->prefix_name_en} {$user->name_en} {$user->last_name_en}";
                                } else {
                                    return 'ไม่มีข้อมูลชื่อภาษาอังกฤษ';
                                }
                            }),
                    ]),

                    Stack::make([
                        TextColumn::make('email')->icon('heroicon-m-envelope')->iconColor('warning')->copyable()
                            ->copyMessage('คัดลอกแล้ว')->copyMessageDuration(1500)->searchable()->sortable(),
                        TextColumn::make('userHasoneResume.tel')->icon('heroicon-m-phone')->iconColor('primary')->default('ไม่ได้ระบุ'),
                    ])->space(1),
                    Stack::make([
                        TextColumn::make('userHasoneIdcard.date_of_birth')
                            ->buddhistDate('d M Y')
                            ->icon('heroicon-m-cake')
                            ->iconColor(Color::hex('#f05ff0')),
                        //->default(Carbon::parse('0000-00-00')->subYears(-543)->format('d M Y')),
                        TextColumn::make('ageidcard')
                            ->icon('heroicon-m-identification')
                            ->iconColor(Color::hex('#0ff')),
                    ])->space(1),
                    SelectColumn::make('role_id')
                        ->label('ระดับพนักงาน')
                        ->options(function ($record) {
                            //dump($record->where('role_id', [1,2])->exists());
                            $user = auth()->user();
                            if ($user->role_id == 1) {
                                // super admin เห็นทุก role
                                return Role::where('active', 1)->pluck('name', 'id');
                            }
                            if ($user->role_id === 2) {
                                if (in_array($record->role_id, [1, 2])) {
                                    return Role::where('active', 1)->pluck('name', 'id');
                                } else {
                                    return Role::whereIn('id', [3, 4])->pluck('name', 'id');
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
                        ->placeholder('เลือกระดับพนักงาน'),
                ])->From('sm'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->tooltip('ดูรายละเอียด')->icon('heroicon-m-eye')->hiddenLabel(),
                DeleteAction::make()->tooltip('ลบพนักงาน')->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
