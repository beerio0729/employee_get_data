<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Form;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Component;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Filament\Schemas\Components\EmbeddedSchema;

use function Termwind\style;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('filament-panels::auth/pages/login.form.username.label'))
            ->required()
            ->placeholder(__('filament-panels::auth/pages/login.form.username.placeholder'))
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $username_type = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'tel';

        return [
            $username_type => $data['username'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    protected function getFormActions(): array
    {   $svg = '
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <!-- กรอบสีเขียว -->
            <rect width="512" height="512" rx="90" fill="#00C300"></rect>

            <!-- คำพูดสีขาว -->
            <path fill="#FFFFFF" d="M441.6 233.5c0-83.4-83.7-151.3-186.4-151.3S68.8 150.1 68.8 233.5c0 74.7 66.3 137.4 155.9 149.3 21.8 4.7 19.3 12.7 14.4 42.1-.8 4.7-3.8 18.4 16.1 10.1s107.3-63.2 146.5-108.2c27-29.7 39.9-59.8 39.9-93.1z"/>

            <!-- ตัวอักษร LINE สีเขียว (เปลี่ยนเป็นสีขาวตามต้องการ) -->
            <path fill="#00C300" d="M311 196.8l0 81.3c0 2.1-1.6 3.7-3.7 3.7l-13 0c-1.3 0-2.4-.7-3-1.5L254 230 254 278.2c0 2.1-1.6 3.7-3.7 3.7l-13 0c-2.1 0-3.7-1.6-3.7-3.7l0-81.3c0-2.1 1.6-3.7 3.7-3.7l12.9 0c1.1 0 2.4 .6 3 1.6l37.3 50.3 0-48.2c0-2.1 1.6-3.7 3.7-3.7l13 0c2.1-.1 3.8 1.6 3.8 3.5l0 .1z"/>
            <path fill="#00C300" d="M217.3 193.1l-13 0c-2.1 0-3.7 1.6-3.7 3.7l0 81.3c0 2.1 1.6 3.7 3.7 3.7l13 0c2.1 0 3.7-1.6 3.7-3.7l0-81.3c0-1.9-1.6-3.7-3.7-3.7z"/>
            <path fill="#00C300" d="M185.9 261.2l-35.6 0 0-64.4c0-2.1-1.6-3.7-3.7-3.7l-13 0c-2.1 0-3.7 1.6-3.7 3.7l0 81.3c0 1 .3 1.8 1 2.5 .7 .6 1.5 1 2.5 1l52.2 0c2.1 0 3.7-1.6 3.7-3.7l0-13c0-1.9-1.6-3.7-3.5-3.7l.1 0z"/>
            <path fill="#00C300" d="M379.6 193.1l-52.3 0c-1.9 0-3.7 1.6-3.7 3.7l0 81.3c0 1.9 1.6 3.7 3.7 3.7l52.2 0c2.1 0 3.7-1.6 3.7-3.7l0-13.1c0-2.1-1.6-3.7-3.7-3.7l-35.5 0 0-13.6 35.5 0c2.1 0 3.7-1.6 3.7-3.7l0-13.1c0-2.1-1.6-3.7-3.7-3.7l-35.5 0 0-13.7 35.5 0c2.1 0 3.7-1.6 3.7-3.7l0-13c-.1-1.9-1.7-3.7-3.7-3.7z"/>
        </svg>';
        
        return [
            ...parent::getFormActions(),
            Action::make('line_login')
                ->label('Login with LINE')
                //->color(Color::hex('#0ff'))
                ->color('success')
                ->url('/auth/line/redirect') // ใส่ route ของคุณเอง
                ->icon(new HtmlString($svg)),

        ];
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-login')
                    ->view("filament.actions.line-login"),
            ])
            ->visible(fn(): bool => blank($this->userUndertakingMultiFactorAuthentication));
    }
}
