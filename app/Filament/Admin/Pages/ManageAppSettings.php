<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigation;
use App\Settings\AppSettings;
use App\Settings\PlatformSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use App\Models\AppAsset;
use App\Models\Banner;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Lang;

class ManageAppSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    protected static string $settings = AppSettings::class;

    public static function getNavigationLabel(): string
    {
        return __('custom.nav.section.app_settings');
    }

    public function getTitle(): string
    {
        return __('custom.nav.section.app_settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::APP_SETTINGS_GROUP);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('settings_tabs')
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(__('custom.settings.app.section.information'))
                            ->schema([
                                TextInput::make('app_version')
                                    ->required()
                                    ->label(__('custom.settings.app.version')),
                                Toggle::make('resumes_active')
                                    ->required()
                                    ->label(__('custom.settings.app.resumes')),
                                Toggle::make('bac_solutions_active')
                                    ->required()
                                    ->label(__('custom.settings.app.bac_solutions')),
                                Toggle::make('cards_tools_active')
                                    ->required()
                                    ->label(__('custom.settings.app.cards_tools')),
                            ]),
                        Tab::make(__('custom.settings.tabs.banners_assets'))
                            ->schema([
                                Section::make(__('custom.tabs.all'))
                                    ->schema([
                                        \Filament\Forms\Components\Placeholder::make('banner_link')
                                            ->label('')
                                            ->content(new \Illuminate\Support\HtmlString('<div class="flex gap-4"><a href="' . \App\Filament\Admin\Resources\BannerResource::getUrl('index') . '" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded shadow">' . __('custom.models.banners') . '</a> <a href="' . \App\Filament\Admin\Resources\AppAssetResource::getUrl('index') . '" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded shadow">' . __('custom.models.division') . '</a></div>')),
                                    ]),
                            ]),
                        Tab::make(__('custom.settings.tabs.tool_images'))
                            ->schema([
                                Section::make('بطاقات بيان')
                                    ->schema([
                                        FileUpload::make('tool_cards_grid')->label('صورة الشبكة')->image()->directory('tools')->preserveFilenames(),
                                        FileUpload::make('tool_cards_list')->label('صورة القائمة')->image()->directory('tools')->preserveFilenames(),
                                    ])->columns(2),
                                Section::make('ملخصات و مراجعات')
                                    ->schema([
                                        FileUpload::make('tool_resumes_grid')->label('صورة الشبكة')->image()->directory('tools')->preserveFilenames(),
                                        FileUpload::make('tool_resumes_list')->label('صورة القائمة')->image()->directory('tools')->preserveFilenames(),
                                    ])->columns(2),
                                Section::make('حلول البكالوريا')
                                    ->schema([
                                        FileUpload::make('tool_bac_solutions_grid')->label('صورة الشبكة')->image()->directory('tools')->preserveFilenames(),
                                        FileUpload::make('tool_bac_solutions_list')->label('صورة القائمة')->image()->directory('tools')->preserveFilenames(),
                                    ])->columns(2),
                                Section::make('المؤقت البومودورو')
                                    ->schema([
                                        FileUpload::make('tool_pomodoro_grid')->label('صورة الشبكة')->image()->directory('tools')->preserveFilenames(),
                                        FileUpload::make('tool_pomodoro_list')->label('صورة القائمة')->image()->directory('tools')->preserveFilenames(),
                                    ])->columns(2),
                                Section::make('حاسبة الدرجات')
                                    ->schema([
                                        FileUpload::make('tool_grade_calc_grid')->label('صورة الشبكة')->image()->directory('tools')->preserveFilenames(),
                                        FileUpload::make('tool_grade_calc_list')->label('صورة القائمة')->image()->directory('tools')->preserveFilenames(),
                                    ])->columns(2),
                                Section::make(__('custom.models.questions'))
                                    ->schema([
                                        FileUpload::make('tool_ai_planner_grid')->label(__('custom.division.create.section.image'))->image()->directory('tools')->preserveFilenames(),
                                        FileUpload::make('tool_ai_planner_list')->label(__('custom.division.create.section.image'))->image()->directory('tools')->preserveFilenames(),
                                    ])->columns(2),
                            ]),
                        Tab::make(__('custom.settings.tabs.tour'))
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
                                            ->label(__('custom.settings.tito.active')),
                                        TextInput::make('tito_api_key')
                                            ->password()
                                            ->revealable()
                                            ->label(__('custom.settings.tito.api_key')),
                                        Toggle::make('tito_strict_mode')
                                            ->label(__('custom.settings.tito.strict_mode'))
                                            ->helperText(__('custom.settings.tito.strict_mode_hint')),
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
                                            ->default([])
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
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
                                            ->label(__('custom.settings.tito.persona')),
                                    ]),
                            ]),
                        Tab::make(__('custom.settings.tabs.platform'))
                            ->schema([
                                Tabs::make('Platform Settings Inline')
                                    ->columnSpanFull()
                                    ->tabs([
                                        Tab::make(__('custom.settings.platform.section.social_media'))
                                            ->schema([
                                                TextInput::make('platform.instagram_url')->label(__('custom.settings.platform.instagram.url'))->url(),
                                                Toggle::make('platform.instagram_active')->label(__('custom.settings.platform.instagram.active')),
                                                TextInput::make('platform.facebook_url')->label(__('custom.settings.platform.facebook.url'))->url(),
                                                Toggle::make('platform.facebook_active')->label(__('custom.settings.platform.facebook.active')),
                                                TextInput::make('platform.tiktok_url')->label(__('custom.settings.platform.tiktok.url'))->url(),
                                                Toggle::make('platform.tiktok_active')->label(__('custom.settings.platform.tiktok.active')),
                                                TextInput::make('platform.youtube_url')->label(__('custom.settings.platform.youtube.url'))->url(),
                                                Toggle::make('platform.youtube_active')->label(__('custom.settings.platform.youtube.active')),
                                                TextInput::make('platform.linkedin_url')->label(__('custom.settings.platform.linkedin.url'))->url(),
                                                Toggle::make('platform.linkedin_active')->label(__('custom.settings.platform.linkedin.active')),
                                            ])->columns(2),
                                        Tab::make(__('custom.settings.platform.section.users_scope'))
                                            ->schema([
                                                DatePicker::make('platform.platform_active_from')->label(__('custom.settings.platform.users_scope.active_from')),
                                                DatePicker::make('platform.platform_active_to')->label(__('custom.settings.platform.users_scope.active_to')),
                                            ])->columns(2),
                                    ]),
                            ]),
                        Tab::make(__('custom.settings.payment.section.information'))
                            ->schema([
                                TextInput::make('payment_name')
                                    ->required()
                                    ->label(__('custom.settings.payment.name')),
                                TextInput::make('payment_number')
                                    ->required()
                                    ->rules(['numeric', 'digits:20'])
                                    ->label(__('custom.settings.payment.number')),
                                Toggle::make('payment_active')
                                    ->required()
                                    ->label(__('custom.settings.payment.active')),
                                Toggle::make('chargily_payment_active')
                                    ->required()
                                    ->label(__('custom.settings.payment.chargily_active')),
                            ]),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $platformSettings = app(PlatformSettings::class);
        $data['platform'] = [
            'instagram_url' => $platformSettings->instagram_url,
            'instagram_active' => $platformSettings->instagram_active,
            'facebook_url' => $platformSettings->facebook_url,
            'facebook_active' => $platformSettings->facebook_active,
            'tiktok_url' => $platformSettings->tiktok_url,
            'tiktok_active' => $platformSettings->tiktok_active,
            'youtube_url' => $platformSettings->youtube_url,
            'youtube_active' => $platformSettings->youtube_active,
            'linkedin_url' => $platformSettings->linkedin_url,
            'linkedin_active' => $platformSettings->linkedin_active,
            'platform_active_from' => $platformSettings->platform_active_from,
            'platform_active_to' => $platformSettings->platform_active_to,
        ];
        return $data;
    }

    protected function handleRegistration(array $data): void
    {
        if (isset($data['platform'])) {
            $platformSettings = app(PlatformSettings::class);
            $platformSettings->instagram_url = $data['platform']['instagram_url'];
            $platformSettings->instagram_active = $data['platform']['instagram_active'];
            $platformSettings->facebook_url = $data['platform']['facebook_url'];
            $platformSettings->facebook_active = $data['platform']['facebook_active'];
            $platformSettings->tiktok_url = $data['platform']['tiktok_url'];
            $platformSettings->tiktok_active = $data['platform']['tiktok_active'];
            $platformSettings->youtube_url = $data['platform']['youtube_url'];
            $platformSettings->youtube_active = $data['platform']['youtube_active'];
            $platformSettings->linkedin_url = $data['platform']['linkedin_url'];
            $platformSettings->linkedin_active = $data['platform']['linkedin_active'];
            $platformSettings->platform_active_from = $data['platform']['platform_active_from'];
            $platformSettings->platform_active_to = $data['platform']['platform_active_to'];
            $platformSettings->save();
        }
        
        parent::handleRegistration($data);
    }
}
