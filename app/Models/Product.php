<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'item_code',
        'price',
        'stock_qty',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_qty' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isLowStock(int $threshold = 5): bool
    {
        return $this->stock_qty <= $threshold;
    }
}
