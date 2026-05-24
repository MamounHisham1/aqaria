<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'area_sqm' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'property_type' => 'required|in:apartment,villa,townhouse,commercial',
            'listing_type' => 'required|in:sale,rent',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_phone' => 'required|string|max:20',
            'contact_whatsapp' => 'nullable|string|max:20',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'amenities' => 'nullable|array|max:20',
            'amenities.*' => 'string|max:100',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'description' => strip_tags($this->description),
            'address' => strip_tags($this->address),
            'city' => strip_tags($this->city),
            'district' => strip_tags($this->district),
        ]);
    }
}
