<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
        'total_fine',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'total_fine' => 'decimal:2',
    ];

    public const STATUS_DIPINJAM = 'dipinjam';

    public const STATUS_TERLAMBAT = 'terlambat';

    public const STATUS_DIKEMBALIKAN = 'dikembalikan';

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_DIPINJAM, self::STATUS_TERLAMBAT], true);
    }
}