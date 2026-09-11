<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn',
        'title',
        'author',
        'publisher',
        'publication_year',
        'category_id',
        'stock',
        'available_stock',
        'photo',
        'description',
        'rack_location',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'stock' => 'integer',
        'available_stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function coverUrl(): string
    {
        return $this->photo ? asset('storage/'.$this->photo) : asset('img/book-placeholder.svg');
    }

    public function isAvailable(): bool
    {
        return $this->available_stock > 0;
    }

    public function stockStatus(): string
    {
        if ($this->available_stock <= 0) {
            return 'habis';
        }

        if ($this->available_stock <= 5) {
            return 'menipis';
        }

        return 'tersedia';
    }
}