<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'route_name',
        'path',
        'action_label',
        'status_code',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(function (UserActivityLog $log): void {
            $keep = (int) config('user_activity.max_logs_per_user', 200);
            if ($keep < 10) {
                return;
            }
            $count = static::where('user_id', $log->user_id)->count();
            if ($count <= $keep) {
                return;
            }
            $deleteCount = $count - $keep;
            $ids = static::where('user_id', $log->user_id)
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit($deleteCount)
                ->pluck('id');
            if ($ids->isNotEmpty()) {
                static::whereIn('id', $ids)->delete();
            }
        });
    }
}
