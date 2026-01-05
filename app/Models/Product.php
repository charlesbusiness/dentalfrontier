<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * Get the wishlists that include this product.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the users who have this product in their wishlist.
     */
    public function wishlistedByUsers()
    {
        return $this->belongsToMany(User::class, 'wishlists')
            ->withTimestamps();
    }

    public function scopeFilter($query, array $filters)
    {
        $query
            ->when(
                $filters['name'] ?? null,
                fn($q, $name) =>
                $q->where('name', 'LIKE', "%{$name}%")
            )
            ->when(
                $filters['min_price'] ?? null,
                fn($q, $price) =>
                $q->where('price', '>=', $price)
            )
            ->when(
                $filters['max_price'] ?? null,
                fn($q, $price) =>
                $q->where('price', '<=', $price)
            );
    }
}
