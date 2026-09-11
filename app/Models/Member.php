<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_code',
        'name',
        'gender',
        'phone',
        'address',
        'status',
    ];

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_TIDAK_AKTIF = 'tidak_aktif';

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }
}