<?php

namespace App\Filament\Resources\Users\Tables;

use Carbon\Carbon;
use App\Models\Employee;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Split::make([
                    // // TextColumn::make('#')
                    // //     ->alignment(Alignment::End)
                    // //     ->rowIndex()
                    // //     ->grow(false)
                    // //     ->visibleFrom('sm')
                    //     ->extraAttributes(['style' => 'width:20px']),
                    ImageColumn::make('userHasoneEmployee.image')
                        ->disk('public')
                        ->simpleLightbox()
                        ->circular()
                        ->grow(false),
                    TextColumn::make('full_name')->label('ชื่อ')->searchable()->sortable()
                        ->default(function () {
                            $user = Auth::user()->userHasoneEmployee;
                            if($user) {
                                return "{$user->prefix_name} {$user->name} {$user->last_name}";
                            } else {
                                return 'ไม่ได้ระบุ';
                            }
                        }),
                    Stack::make([
                        TextColumn::make('email')->icon('heroicon-m-envelope')->iconColor('warning')->copyable()
                            ->copyMessage('คัดลอกแล้ว')->copyMessageDuration(1500)->searchable()->sortable(),
                        TextColumn::make('userHasoneEmployee.tel')->icon('heroicon-m-phone')->iconColor('primary')->default('ไม่ได้ระบุ'),
                    ])->space(1),
                    Stack::make([
                        TextColumn::make('userHasoneEmployee.date_of_birth')
                            ->buddhistDate('d M Y')
                            ->icon('heroicon-m-cake')
                            ->iconColor(Color::hex('#f05ff0')),
                            //->default(Carbon::parse('0000-00-00')->subYears(-543)->format('d M Y')),
                        TextColumn::make('age')
                            ->icon('heroicon-m-identification')
                            ->iconColor(Color::hex('#0ff'))
                            ->prefix('อายุ : '),
                    ])->space(1),

                    //->defaultImageUrl(url('storage/user.png')),
                ])->From('sm'),

                Panel::make([
                    Grid::make(3)
                        ->schema([
                            Stack::make([
                                TextColumn::make('userHasonelocation.address')->label('ทีอยู่')->searchable()->sortable()
                                    ->prefix('ที่อยู่: ')->default('ไม่ได้ระบุ'),
                                TextColumn::make('userHasonelocation.empBelongtosubdistrict.name_th')->label('ตำบล')->searchable()->sortable()
                                    ->prefix('แขวง/ตำบล : ')->default('ไม่ได้ระบุ'),
                                TextColumn::make('userHasonelocation.empBelongtodistrict.name_th')->label('อำเภอ')->searchable()->sortable()
                                    ->prefix('เขต/อำเภอ : ')->default('ไม่ได้ระบุ'),
                                TextColumn::make('userHasonelocation.empBelongtoprovince.name_th')->label('จังหวัด')->searchable()->sortable()
                                    ->prefix('จังหวัด : ')->default('ไม่ได้ระบุ'),
                                TextColumn::make('userHasonelocation.zipcode')->label('รหัสไปรษณีย์')->searchable()->sortable()
                                    ->prefix('รหัสไปรษณีย์ : ')->default('ไม่ได้ระบุ'),
                            ])->space(1)->columnSpan(1),
                            Stack::make([
                                TextColumn::make('work_experience_summary')->html()->default('ไม่ได้ระบุ')->prefix('ประวัดิการทำงาน : '),
                            ])->space(1)->columnSpan(2),
                        ])
                ])->collapsed(true)
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
