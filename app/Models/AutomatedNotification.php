<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomatedNotification extends Model
{
    protected $fillable = [
        'name',
        'trigger_type',
        'title',
        'body',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
