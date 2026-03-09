<x-filament-panels::page>
    <x-filament::card>
        <div class="p-4 space-y-4">
            <h2 class="text-xl font-bold text-primary-600">إدارة الإشعارات العامة والخاصة بالمنصة</h2>
            <p class="text-gray-600 dark:text-gray-400">
                من خلال هذه الصفحة يمكنك إرسال إشعار لحظي لجميع المستخدمين (وخاصة الطلاب) في المنصة. 
                سيتم إرسال الإشعار لجميع الأجهزة النشطة التي يمتلك أصحابها رموز FCM صحيحة.
            </p>
        </div>
    </x-filament::card>

    <div class="mt-6">
        {{ $this->form }}
    </div>

    <div class="mt-4 flex justify-end">
        <x-filament::button wire:click="send" icon="heroicon-o-paper-airplane" size="lg">
            إرسال الإشعار لجميع المستخدمين الآن
        </x-filament::button>
    </div>
</x-filament-panels::page>
