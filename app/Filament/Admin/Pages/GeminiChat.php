<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\AdminNavigation;
use App\Services\GeminiAiService;
use Filament\Pages\Page;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class GeminiChat extends Page
{
    protected static ?string $navigationIcon = AdminNavigation::GEMINI_CHAT_PAGE['icon'];

    protected static string $view = 'filament.admin.pages.gemini-chat';

    protected static ?string $title = 'مساعد Gemini الذكي';

    protected static ?string $navigationLabel = 'مساعد Gemini';

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::GEMINI_CHAT_PAGE['group'] ?? 'custom.nav.section.content');
    }

    protected static ?int $navigationSort = AdminNavigation::GEMINI_CHAT_PAGE['sort'];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public $message = '';
    public $chatHistory = [];
    public $loading = false;
    public $selectedModel = 'gemini-1.5-flash';

    public function mount()
    {
        $this->chatHistory = session()->get('gemini_chat_history', []);
        $this->selectedModel = \App\Models\GeminiSetting::where('key', 'model_name')->first()?->value ?? 'gemini-1.5-flash';
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) return;

        $userMessage = $this->message;
        $this->chatHistory[] = ['role' => 'user', 'content' => $userMessage];
        $this->message = '';
        $this->loading = true;

        try {
            $response = GeminiAiService::chat($userMessage, $this->selectedModel);
            $this->chatHistory[] = ['role' => 'assistant', 'content' => $response];
            
            session()->put('gemini_chat_history', $this->chatHistory);
            
            $this->dispatch('scroll-to-bottom');
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في الاتصال')
                ->body($e->getMessage())
                ->danger()
                ->send();
            
            // Remove the user message if it failed or keep it? Let's keep it but show error.
        }

        $this->loading = false;
    }

    public function clearChat()
    {
        $this->chatHistory = [];
        session()->forget('gemini_chat_history');
        
        Notification::make()
            ->title('تم مسح المحادثة')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear')
                ->label('مسح المحادثة')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(fn() => $this->clearChat()),
        ];
    }
}
