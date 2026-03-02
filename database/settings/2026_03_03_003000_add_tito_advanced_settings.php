<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.tito_qa_list', '["كم سعر الاشتراك؟", "ما هي المواد المتاحة؟", "ما هو هدف التطبيق؟", "كيف أتواصل معكم؟"]');
        $this->migrator->add('default.tito_app_goal', 'تطبيق تيسير يهدف لتبسيط دروس البكالوريا لجميع الشعب في الجزائر عبر فيديوهات وتمارين تفاعلية.');
        $this->migrator->add('default.tito_subscription_price', 'اشتراك الفصل الواحد بـ 1000 دج، أو العام كاملاً بـ 2500 دج.');
        $this->migrator->add('default.tito_available_materials', 'كل مواد البكالوريا حسب الشعبة: رياضيات، علوم، فيزياء، لغات، أدب...');
        $this->migrator->add('default.tito_social_links', 'فيسبوك وتيك توك تحت اسم: Tayssir Bac');
        $this->migrator->add('default.tito_strict_mode', true);
    }
};
