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
                ])
                ->action(function (array $data): void {
                    // chunk and notify
                    User::whereHas('roles', function ($q) {
                        $q->where('name', 'student');
                    })->chunk(100, function ($users) use ($data) {
                        foreach ($users as $user) {
                            $user->notify(new \App\Notifications\CustomUserNotification($data['title'], $data['body']));
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
