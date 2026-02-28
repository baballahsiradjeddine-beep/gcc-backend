<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppAsset extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'image_url',
        'version_hash',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto-regenerate version_hash whenever image_url changes
    protected static function booted(): void
    {
        static::saving(function (AppAsset $asset) {
            if ($asset->isDirty('image_url')) {
                $asset->version_hash = Str::random(12);
            }
        });
    }

    // ─── Static asset keys (used in Flutter) ───────────────────────────────
    public const KEY_SUBSCRIBE_BANNER        = 'subscribe_banner';
    public const KEY_CHALLENGE_CHARACTER     = 'challenge_character';
    public const KEY_HOME_HERO               = 'home_hero';
    public const KEY_EMPTY_STATE             = 'empty_state';
    public const KEY_APP_LOGO               = 'app_logo';

    // Tito Variants
    public const KEY_TITO_AUTH               = 'tito_auth';
    public const KEY_TITO_LOGIN_ADVICE       = 'tito_login_advice';
    public const KEY_TITO_GENERIC            = 'tito_generic';
    public const KEY_TITO_ANGRY              = 'tito_angry';
    public const KEY_TITO_GOOD               = 'tito_good';
    public const KEY_TITO_PERFECT            = 'tito_perfect';
    
    // Exercises
    public const KEY_TITO_GOOD_EXERCISE      = 'tito_good_exercise';
    public const KEY_TITO_BAD_MESSAGE        = 'tito_bad_message';
    public const KEY_TITO_BAD                = 'tito_bad';
    public const KEY_TITO_AVERAGE            = 'tito_average';
    public const KEY_TITO_AVERAGE_MESSAGE    = 'tito_average_message';

    // Pomodoro
    public const KEY_TITO_POMODORO_FIRST     = 'tito_pomodoro_first';
    public const KEY_TITO_POMODORO_STOP      = 'tito_pomodoro_stop';
    public const KEY_TITO_POMODORO_DONE      = 'tito_pomodoro_done';

    // Subscription status
    public const KEY_TITO_SUB_PENDING        = 'tito_sub_pending';
    public const KEY_TITO_SUB_FAILURE        = 'tito_sub_failure';
    public const KEY_TITO_SUB_GOOD           = 'tito_sub_good';

    // All known keys with default labels
    public const DEFAULT_ASSETS = [
        self::KEY_SUBSCRIBE_BANNER    => ['label' => 'بنر الاشتراك',      'description' => 'الصورة في قسم الاشتراك في الصفحة الرئيسية'],
        self::KEY_CHALLENGE_CHARACTER => ['label' => 'شخصية التحديات',    'description' => 'شخصية الدولفين في صفحة التحديات'],
        self::KEY_HOME_HERO           => ['label' => 'صورة الصفحة الرئيسية', 'description' => 'صورة البطل في الصفحة الرئيسية كالشعار الترحيبي'],
        self::KEY_EMPTY_STATE         => ['label' => 'حالة فارغة',         'description' => 'صورة تظهر عند عدم وجود بيانات'],
        self::KEY_APP_LOGO            => ['label' => 'شعار التطبيق',       'description' => 'الشعار الرئيسي للتطبيق'],

        self::KEY_TITO_AUTH           => ['label' => 'شخصية التسجيل',      'description' => 'تظهر في شاشات تسجيل الدخول وإنشاء حساب (البوردينج)'],
        self::KEY_TITO_LOGIN_ADVICE   => ['label' => 'نصائح تسجيل الدخول', 'description' => 'الشخصية التي تقدم نصائح اثناء التحميل'],
        self::KEY_TITO_GENERIC        => ['label' => 'تيتو الافتراضي',     'description' => 'الوجه العادي للشخصية'],
        self::KEY_TITO_ANGRY          => ['label' => 'تيتو الغاضب/السيء',   'description' => 'يظهر عند تقييم مستوى ضعيف أو في الخطر'],
        self::KEY_TITO_GOOD           => ['label' => 'تيتو الجيد',          'description' => 'يظهر عند مستوى التحسن الجيد وفي لوحة الأيام'],
        self::KEY_TITO_PERFECT        => ['label' => 'تيتو الممتاز',        'description' => 'يظهر عند الوصول لمستوى متقدم (العلّامة)'],

        self::KEY_TITO_GOOD_EXERCISE  => ['label' => 'نتيجة تمرين ممتاز',  'description' => 'الصورة بعد إنهاء الأسئلة بنتيجة عالية'],
        self::KEY_TITO_BAD_MESSAGE    => ['label' => 'نتيجة تمرين سيئة',   'description' => 'الصورة التي تخبرك بدرجة سيئة'],
        self::KEY_TITO_BAD            => ['label' => 'تمرين رسوب',        'description' => 'رسوب تام بالتمرين'],
        self::KEY_TITO_AVERAGE        => ['label' => 'تمرين متوسط',       'description' => 'النتيجة متوسطة بالتمارين'],
        self::KEY_TITO_AVERAGE_MESSAGE=> ['label' => 'رسالة تمرين متوسط', 'description' => 'رسالة إضافية للمتوسط'],

        self::KEY_TITO_POMODORO_FIRST => ['label' => 'بومودورو (بداية)',  'description' => 'شخصية بومودورو للبدء'],
        self::KEY_TITO_POMODORO_STOP  => ['label' => 'بومودورو (توقف)',   'description' => 'شخصية بومودورو عند التوقف'],
        self::KEY_TITO_POMODORO_DONE  => ['label' => 'بومودورو (انتهاء)', 'description' => 'شخصية بومودورو عند الانتهاء'],

        self::KEY_TITO_SUB_PENDING    => ['label' => 'الدفع (قيد الانتظار)','description' => 'الصورة في التنبيهات حول انتظار التأكيد'],
        self::KEY_TITO_SUB_FAILURE    => ['label' => 'الدفع (فشل)',        'description' => 'تنبيه حول فشل الاشتراك'],
        self::KEY_TITO_SUB_GOOD       => ['label' => 'الدفع (اكتمل بنجاح)','description' => 'تنبيه نجاح تفعيل الاشتراك'],
    ];
}
