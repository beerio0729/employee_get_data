<?php

namespace App\Filament\Pages\Auth;

use Carbon\Carbon;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Hidden;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        $parentForm = parent::form($schema);
        $mycomponents = $parentForm->getComponents();
        $newcomponenets = [
            Hidden::make('role_id')->default(3),
            TextInput::make('tel')
                ->columnSpan(1)
                ->placeholder(__('filament-panels::auth/pages/register.form.tel.placeholder'))
                ->mask('9999999999')
                ->label(__('filament-panels::auth/pages/register.form.tel.label'))
                ->tel()
                ->afterLabel(__('filament-panels::auth/pages/register.form.tel.afterlabel'))
                ->required()
                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
        ];
        $mycomponents = array_merge(
            array_slice($mycomponents, 0, 1),
            $newcomponenets,
            array_slice($mycomponents, 1)
        );
        return $schema->schema($mycomponents);
    }

    protected function handleRegistration(array $data): Model
    {
        $user = parent::handleRegistration($data);
        DB::transaction(function () use ($user) {
            $workStatus = $user->userHasoneWorkStatus()->create([
                'work_status_def_detail_id' => 1,
            ]);

            $workStatus->workStatusHasonePreEmp()->create([
                'applied_at' => now()->locale('th'),
            ]);

            $user->userHasoneHistory()->create([
                'data' => [[
                    'event' => 'applied',
                    'description' => 'สมัครครั้งแรกสำเร็จ',
                    'date' => Carbon::now()->format('Y-m-d H:i:s'),
                ]],
            ]);
        }, attempts: 5);
        return $user;
    }
}
