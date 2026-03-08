<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Badge extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'min_points',
        'max_points',
        'color',
        'rank_order',
    ];

    public function getIconAttribute(): ?string
    {
        return $this->getFirstMediaUrl('badge_icon');
    }

    public static function getByPoints(int $points)
    {
        return self::where('min_points', '<=', $points)
            ->where(function ($query) use ($points) {
                $query->where('max_points', '>=', $points)
                    ->orWhereNull('max_points');
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }
}
