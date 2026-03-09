<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Clusters\NotificationCluster;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;

class ManageNotifications extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static ?string $cluster = NotificationCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static string $view = 'filament.admin.pages.manage-notifications';

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function getNavigationSort(): ?int
    {
        return AdminNavigation::MANAGE_NOTIFICATIONS_PAGE['sort'];
    }

    public static function getNavigationLabel(): string
    {
        return 'إرسال إشعار يدوي';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('إرسال إشعار يدوي فوري')
                    ->description('سيتم إرسال هذا الإشعار لجميع مستخدمي التطبيق المشتركين في نظام الإشعارات.')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان الإشعار')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('body')
                            ->label('محتوى الإشعار')
                            ->required()
                            ->rows(4)
                            ->maxLength(65535),
                        \Filament\Forms\Components\FileUpload::make('image')
                            ->label('صورة الإشعار (مرفق اختياري)')
                            ->image()
                            ->directory('notifications')
                            ->nullable(),
                    ])->columns(1)
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        
        $imageUrl = null;
        if (!empty($data['image'])) {
            $imageUrl = url(\Illuminate\Support\Facades\Storage::url($data['image']));
        }

        // Send notification to all users who have an FCM token
        User::whereNotNull('fcm_token')->chunk(100, function ($users) use ($data, $imageUrl) {
            foreach ($users as $user) {
                $user->notify(new \App\Notifications\CustomUserNotification($data['title'], $data['body'], $imageUrl));
            }
        });
        
        $this->form->fill();

        Notification::make()
            ->title('تم إرسال الإشعار بنجاح لجميع الطلبة!')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'إرسال الإشعارات';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('إرسال الإشعار الآن')
                ->icon('heroicon-o-paper-airplane')
                ->color("primary")
                ->action('send')
        ];
    }
}
