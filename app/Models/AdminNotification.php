<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminNotification extends Model
{
    public const TYPE_NEW_ORDER = 'new_order';
    public const TYPE_LOW_STOCK = 'low_stock';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_DANGER = 'danger';

    protected $fillable = [
        'type',
        'severity',
        'title',
        'message',
        'action_url',
        'reference_type',
        'reference_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'resolved_at'  => 'datetime',
        ];
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AdminNotificationRead::class, 'notification_id');
    }

    public function scopeForTypes(Builder $query, array $types): Builder
    {
        if (empty($types)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('type', $types);
    }

    public function scopeUnreadForUser(Builder $query, int $userId): Builder
    {
        return $query->whereDoesntHave('reads', function (Builder $readQuery) use ($userId) {
            $readQuery->where('user_id', $userId);
        });
    }
}
