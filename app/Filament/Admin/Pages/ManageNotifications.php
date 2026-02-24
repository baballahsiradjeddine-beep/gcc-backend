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

class ManageNotifications extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static string $view = 'filament.admin.pages.manage-notifications';

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::MANAGEMENT_GROUP);
    }

    public static function getNavigationSort(): ?int
    {
        // Placed between user (4) and referral source (6) etc
        return 5;
    }

    public static function getNavigationLabel(): string
    {
        return 'الإشعارات';
    }

    public function getTitle(): string
    {
        return 'إدارة الإشعارات';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_notification_to_all_users')
                ->label('إرسال إشعار للمستخدمين')
                ->icon('heroicon-o-paper-airplane')
                ->color("warning")
                ->form([
                    TextInput::make('title')
                        ->label('عنوان الإشعار')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('body')
                        ->label('محتوى الإشعار')
                        ->required()
                        ->maxLength(65535),
                    \Filament\Forms\Components\FileUpload::make('image')
                        ->label('صورة الإشعار (مرفق اختياري)')
                        ->image()
                        ->directory('notifications')
                        ->nullable(),
                ])
                ->action(function (array $data): void {
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
                    
                    Notification::make()
                        ->title('تم إرسال الإشعار بنجاح لجميع الطلبة!')
                        ->success()
                        ->send();
                })
        ];
    }
}
