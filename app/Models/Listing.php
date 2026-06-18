<?php

namespace App\Models;

use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'description',
    'price',
    'area_sqm',
    'bedrooms',
    'bathrooms',
    'property_type',
    'listing_type',
    'city',
    'district',
    'address',
    'latitude',
    'longitude',
    'contact_phone',
    'contact_whatsapp',
    'is_featured',
    'is_active',
    'images',
    'amenities',
])]
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'area_sqm' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'images' => 'array',
            'amenities' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the views for the listing.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ListingView::class);
    }

    /**
     * Get the clicks for the listing.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(ListingClick::class);
    }

    /**
     * Scope a query to only include active listings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured listings.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    /**
     * Scope a query to filter by listing type.
     */
    public function scopeByListingType($query, string $type)
    {
        return $query->where('listing_type', $type);
    }

    /**
     * Scope a query to filter by property type.
     */
    public function scopeByPropertyType($query, string $type)
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope a query to filter by city.
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Scope a query to search listings.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%")
                ->orWhere('district', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceBetween($query, ?float $min, ?float $max)
    {
        return $query->when($min, fn ($q) => $q->where('price', '>=', $min))
            ->when($max, fn ($q) => $q->where('price', '<=', $max));
    }

    /**
     * Scope a query to filter by area range.
     */
    public function scopeAreaBetween($query, ?int $min, ?int $max)
    {
        return $query->when($min, fn ($q) => $q->where('area_sqm', '>=', $min))
            ->when($max, fn ($q) => $q->where('area_sqm', '<=', $max));
    }

    /**
     * Scope a query to filter by number of bedrooms.
     */
    public function scopeWithBedrooms($query, ?int $bedrooms)
    {
        return $query->when($bedrooms, fn ($q) => $q->where('bedrooms', '>=', $bedrooms));
    }

    /**
     * Scope a query to filter by number of bathrooms.
     */
    public function scopeWithBathrooms($query, ?int $bathrooms)
    {
        return $query->when($bathrooms, fn ($q) => $q->where('bathrooms', '>=', $bathrooms));
    }

    /**
     * Get the price formatted with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, '.', ',').' EGP';
    }

    /**
     * Get the primary image URL.
     */
    public function getPrimaryImageAttribute(): ?string
    {
        $images = $this->images;

        if (empty($images) || ! is_array($images)) {
            return null;
        }

        return $images[0] ?? null;
    }

    /**
     * Get the view count.
     */
    public function getViewsCountAttribute(): int
    {
        return $this->views()->count();
    }

    /**
     * Get the click count.
     */
    public function getClicksCountAttribute(): int
    {
        return $this->clicks()->count();
    }
}
