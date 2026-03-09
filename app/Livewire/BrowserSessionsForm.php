<?php

namespace App\Livewire;

use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Joaopaulolndev\FilamentEditProfile\Livewire\BrowserSessionsForm as BaseBrowserSessionsForm;

class BrowserSessionsForm extends BaseBrowserSessionsForm
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('filament-edit-profile::default.browser_section_title'))
                    ->description(__('filament-edit-profile::default.browser_section_description'))
                    ->schema([
                        Forms\Components\ViewField::make('browserSessions')
                            ->label(__(__('filament-edit-profile::default.browser_section_title')))
                            ->hiddenLabel()
                            ->view('filament-edit-profile::forms.components.browser-sessions')
                            ->viewData(['data' => self::getSessions()]),
                        Actions::make([
                            Actions\Action::make('deleteBrowserSessions')
                                ->label(__('filament-edit-profile::default.browser_sessions_log_out'))
                                ->requiresConfirmation()
                                ->modalHeading(__('filament-edit-profile::default.browser_sessions_log_out'))
                                ->modalDescription(__('filament-edit-profile::default.browser_sessions_confirm_pass'))
                                ->modalSubmitActionLabel(__('filament-edit-profile::default.browser_sessions_log_out'))
                                ->form([
                                    Forms\Components\TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->label(__('filament-edit-profile::default.password'))
                                        ->required(),
                                ])
                                ->action(function (array $data) {
                                    self::logoutOtherBrowserSessions($data['password']);
                                })
                                ->modalWidth('2xl'),
                        ]),
                    ]),
            ]);
    }
}
