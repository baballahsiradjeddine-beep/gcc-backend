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

    // All known keys with default labels
    public const DEFAULT_ASSETS = [
        self::KEY_SUBSCRIBE_BANNER    => ['label' => 'بنر الاشتراك',      'description' => 'الصورة في قسم الاشتراك في الصفحة الرئيسية'],
        self::KEY_CHALLENGE_CHARACTER => ['label' => 'شخصية التحديات',    'description' => 'شخصية الدولفين في صفحة التحديات'],
        self::KEY_HOME_HERO           => ['label' => 'صورة الصفحة الرئيسية', 'description' => 'صورة البطل في الصفحة الرئيسية'],
        self::KEY_EMPTY_STATE         => ['label' => 'حالة فارغة',         'description' => 'صورة تظهر عند عدم وجود بيانات'],
        self::KEY_APP_LOGO            => ['label' => 'شعار التطبيق',       'description' => 'الشعار الرئيسي للتطبيق'],
    ];
}
