<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class UserLivewire extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public function userInfolist(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema
            ->record($user)
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Tab 1')
                            ->label('ข้อมูลทั่วไป')
                            ->schema([
                                Fieldset::make('ข้อมูลทั่วไป')
                                    ->hiddenLabel()
                                    ->columns(5)
                                    ->contained(false)
                                    ->schema([
                                        TextEntry::make('full_name')
                                            ->label('ชื่อ')
                                            ->default(function () {
                                                $r = auth()->user()->userHasoneResume;
                                                return "{$r?->prefix_name} {$r?->name} {$r?->last_name}";
                                            }),

                                        TextEntry::make('email')->label('อีเมล'),
                                        TextEntry::make('userHasoneResume.id_card')->label('เลขบัตรประชาชน'),
                                    ]),

                            ]),
                        Tab::make('Tab 2')
                            ->schema([
                                // ...
                            ]),
                        Tab::make('Tab 3')
                            ->schema([
                                // ...
                            ]),
                    ])

            ]);
    }

    public function render()
    {
        return view('livewire.user-livewire');
    }
}
