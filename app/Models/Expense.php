<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'store_id',
        'title',
        'category',
        'amount',
        'date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'date'   => 'date',
    ];

    // ─── Category constants ───────────────────────────────────────────────────
    const CATEGORIES = [
        'es_batu'   => 'Es Batu & Pengawet',
        'garam'     => 'Garam Pengering',
        'packing'   => 'Bahan Kemasan',
        'transport' => 'Solar/Transportasi',
        'other'     => 'Operasional Lainnya',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForPeriod($query, string $start, string $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }
}
