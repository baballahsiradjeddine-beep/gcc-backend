<?php

namespace App\Livewire;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditPasswordForm as BaseEditPasswordForm;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditPasswordForm extends BaseEditPasswordForm
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-edit-profile::default.update_password'))
                    ->description(__('filament-edit-profile::default.ensure_your_password'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('Current password')
                                    ->label(__('filament-edit-profile::default.current_password'))
                                    ->password()
                                    ->required()
                                    ->currentPassword()
                                    ->revealable()
                                    ->columnSpanFull(),
                                TextInput::make('password')
                                    ->label(__('filament-edit-profile::default.new_password'))
                                    ->password()
                                    ->required()
                                    ->rule(Password::default())
                                    ->autocomplete('new-password')
                                    ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                                    ->live(debounce: 500)
                                    ->same('passwordConfirmation')
                                    ->revealable(),
                                TextInput::make('passwordConfirmation')
                                    ->label(__('filament-edit-profile::default.confirm_password'))
                                    ->password()
                                    ->required()
                                    ->dehydrated(false)
                                    ->revealable(),
                            ])
                    ]),
            ])
            ->model($this->getUser())
            ->statePath('data');
    }
}
