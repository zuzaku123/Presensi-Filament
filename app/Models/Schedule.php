<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    use HasFactory;
    protected $casts = [
        'is_wfa' => 'boolean',
        'is_banned' => 'boolean',
    ];
    protected $fillable = [
        'user_id',
        'shift_id',
        'office_id',
        'is_wfa',
        'is_banned',
        // 'latitude',
        // 'longitude',
        // 'schedule_latitude',
        // 'schedule_longitude',
        // 'schedule_start_time',
        // 'schedule_end_time',
        // 'start_time',
        // 'end_time',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
