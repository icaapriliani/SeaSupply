<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreMembership extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'role',
        'status',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'owner') {
            return true;
        }

        $perms = $this->permissions ?? [];
        return in_array($permission, $perms, true);
    }
}
