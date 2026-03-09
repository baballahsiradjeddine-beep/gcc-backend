<?php

namespace App\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Joaopaulolndev\FilamentEditProfile\Livewire\EditProfileForm as BaseEditProfileForm;

class EditProfileForm extends BaseEditProfileForm
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('filament-edit-profile::default.profile_information'))
                    ->description(__('filament-edit-profile::default.profile_information_description'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                FileUpload::make(config('filament-edit-profile.avatar_column', 'avatar_url'))
                                    ->label(__('filament-edit-profile::default.avatar'))
                                    ->avatar()
                                    ->imageEditor()
                                    ->disk(config('filament-edit-profile.disk', 'public'))
                                    ->visibility(config('filament-edit-profile.visibility', 'public'))
                                    ->directory(filament('filament-edit-profile')->getAvatarDirectory())
                                    ->rules(filament('filament-edit-profile')->getAvatarRules())
                                    ->hidden(! filament('filament-edit-profile')->getShouldShowAvatarForm())
                                    ->columnSpan([
                                        'default' => 3,
                                        'md' => 1,
                                    ]),
                                Group::make([
                                    TextInput::make('name')
                                        ->label(__('filament-edit-profile::default.name'))
                                        ->required(),
                                    TextInput::make('email')
                                        ->label(__('filament-edit-profile::default.email'))
                                        ->email()
                                        ->required()
                                        ->unique($this->userClass, ignorable: $this->user),
                                ])->columnSpan([
                                    'default' => 3,
                                    'md' => 2,
                                ])
                            ])
                    ]),
            ])
            ->statePath('data');
    }
}
