<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigation;
use App\Settings\AppSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Lang;

class ManageAppSettings extends SettingsPage
{
    protected static ?string $navigationIcon = AdminNavigation::APP_SETTINGS['icon'];

    protected static ?string $title = null;

    protected static ?int $navigationSort = AdminNavigation::APP_SETTINGS['sort'];

    protected static string $settings = AppSettings::class;

    public static function getNavigationLabel(): string
    {
        return Lang::get('custom.settings.app.title');
    }

    public function getTitle(): string
    {
        return Lang::get('custom.settings.app.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::APP_SETTINGS['group']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('settings_tabs')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(Lang::get('custom.settings.app.section.information'))
                            ->schema([
                                TextInput::make('app_version')
                                    ->required()
                                    ->label(Lang::get('custom.settings.app.version')),
                                Toggle::make('resumes_active')
                                    ->required()
                                    ->label(Lang::get('custom.settings.app.resumes')),
                                Toggle::make('bac_solutions_active')
                                    ->required()
                                    ->label(Lang::get('custom.settings.app.bac_solutions')),
                                Toggle::make('cards_tools_active')
                                    ->required()
                                    ->label(Lang::get('custom.settings.app.cards_tools')),
                            ]),
                        Tab::make(Lang::get('custom.settings.payment.section.information'))
                            ->schema([

                                TextInput::make('payment_name')
                                    ->required()
                                    ->label(Lang::get('custom.settings.payment.name')),
                                TextInput::make('payment_number')
                                    ->required()
                                    ->rules(['numeric', 'digits:20'])
                                    ->label(Lang::get('custom.settings.payment.number')),
                                Toggle::make('payment_active')
                                    ->required()
                                    ->label(Lang::get('custom.settings.payment.active')),
                                Toggle::make('chargily_payment_active')
                                    ->required()
                    ->label(Lang::get('custom.settings.payment.chargily_active')),
                            ]),
                        Tab::make('الجولة التجريبية')
                            ->schema([
                                FileUpload::make('tour_material_grid_image')
                                    ->label('صورة المادة (الشبكة - Grid)')
                                    ->image()
                                    ->directory('tour')
                                    ->preserveFilenames(),
                                FileUpload::make('tour_material_list_image')
                                    ->label('صورة المادة (القائمة - List)')
                                    ->image()
                                    ->directory('tour')
                                    ->preserveFilenames(),
                                FileUpload::make('tour_unit_image')
                                    ->label('صورة المحور (Unit)')
                                    ->image()
                                    ->directory('tour')
                                    ->preserveFilenames(),
                                FileUpload::make('tour_chapter_image')
                                    ->label('صورة الدرس (Chapter)')
                                    ->image()
                                    ->directory('tour')
                                    ->preserveFilenames(),
                            ]),
                        Tab::make(Lang::get('custom.settings.tito.title'))
                            ->schema([
                                Section::make('إعدادات الاتصال')
                                    ->schema([
                                        Toggle::make('tito_active')
                                            ->required()
                                            ->label(Lang::get('custom.settings.tito.active')),
                                        TextInput::make('tito_api_key')
                                            ->password()
                                            ->revealable()
                                            ->label(Lang::get('custom.settings.tito.api_key')),
                                        Toggle::make('tito_strict_mode')
                                            ->label(Lang::get('custom.settings.tito.strict_mode'))
                                            ->helperText(Lang::get('custom.settings.tito.strict_mode_hint')),
                                    ])->columns(2),

                                Section::make('محتوى المساعد')
                                    ->schema([
                                        TextInput::make('tito_welcome_message')
                                            ->required()
                                            ->label(Lang::get('custom.settings.tito.welcome_message')),
                                        \Filament\Forms\Components\Repeater::make('tito_qa_list')
                                            ->label('قائمة الأسئلة السريعة')
                                            ->schema([
                                                TextInput::make('label')->label('نص الزر (السؤال)')->required(),
                                                \Filament\Forms\Components\Textarea::make('value')->label('الإجابة التلقائية')->required()->rows(2),
                                            ])
                                            ->columnSpanFull()
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                                            ->afterStateHydrated(function ($component, $state) {
                                                if (is_string($state)) {
                                                    $component->state(json_decode($state, true) ?? []);
                                                }
                                            })
                                            ->dehydrateStateUsing(fn ($state) => $state),
                                        TextInput::make('tito_app_goal')
                                            ->label(Lang::get('custom.settings.tito.app_goal')),
                                        TextInput::make('tito_subscription_price')
                                            ->label(Lang::get('custom.settings.tito.subscription_price')),
                                        TextInput::make('tito_available_materials')
                                            ->label(Lang::get('custom.settings.tito.available_materials')),
                                        TextInput::make('tito_social_links')
                                            ->label(Lang::get('custom.settings.tito.social_links')),
                                    ]),

                                Section::make('التعليمات المتقدمة')
                                    ->schema([
                                        \Filament\Forms\Components\Textarea::make('tito_persona')
                                            ->required()
                                            ->rows(10)
                                            ->columnSpanFull()
                                            ->label(Lang::get('custom.settings.tito.persona')),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
