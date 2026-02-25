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
}
