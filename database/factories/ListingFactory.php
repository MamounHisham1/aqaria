<?php

namespace Database\Factories;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    private static array $cities = ['Cairo', 'Alexandria', 'Giza', 'Sharm El Sheikh', 'Hurghada', 'Luxor', 'Aswan', 'Mansoura', 'Tanta', 'Port Said'];

    private static array $districtsByCity = [
        'Cairo' => ['Maadi', 'Zamalek', 'Heliopolis', 'Nasr City', 'New Cairo', '6th of October', 'Sheikh Zayed', 'Dokki', 'Mohandessin', 'Garden City'],
        'Alexandria' => ['Smouha', 'Sidi Gaber', 'Miami', 'Montaza', 'Stanley', 'Roushdy', 'Kafr Abdo', 'San Stefano'],
        'Giza' => ['Dokki', 'Mohandessin', 'Faisal', 'Haram', 'Agouza'],
        'Sharm El Sheikh' => ['Naama Bay', 'Sharks Bay', 'Hadaba', 'Nabq Bay'],
        'Hurghada' => ['El Gouna', 'Sahl Hasheesh', 'Mamsha', 'Dahar'],
        'Luxor' => ['Karnak', 'West Bank', 'East Bank'],
        'Aswan' => ['City Center', 'Elephantine Island'],
        'Mansoura' => ['University District', 'Toriel', 'City Center'],
        'Tanta' => ['City Center', 'Said Street', 'Stadium District'],
        'Port Said' => ['Port Fouad', 'Al Arab', 'Al Manakh'],
    ];

    private static array $amenities = [
        'Swimming Pool',
        'Gym',
        'Security',
        'Parking',
        'Garden',
        'Balcony',
        'Roof Terrace',
        'Elevator',
        'Central AC',
        'Fireplace',
        'Sea View',
        'Nile View',
        'Maid Room',
        'Built-in Kitchen',
        'Storage Room',
        'Laundry Room',
    ];

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $propertyType = fake()->randomElement(['apartment', 'villa', 'townhouse', 'commercial']);
        $listingType = fake()->randomElement(['sale', 'rent']);
        $city = fake()->randomElement(self::$cities);
        $district = fake()->randomElement(self::$districtsByCity[$city]);
        $bedrooms = $propertyType === 'commercial' ? 0 : fake()->numberBetween(1, 6);
        $bathrooms = $propertyType === 'commercial' ? fake()->numberBetween(1, 2) : max(1, (int) ($bedrooms * 0.7));
        $areaSqm = match ($propertyType) {
            'apartment' => fake()->numberBetween(60, 350),
            'villa' => fake()->numberBetween(200, 800),
            'townhouse' => fake()->numberBetween(150, 400),
            'commercial' => fake()->numberBetween(30, 500),
        };

        $price = match ($listingType) {
            'sale' => match ($propertyType) {
                'apartment' => fake()->numberBetween(500000, 8000000),
                'villa' => fake()->numberBetween(2000000, 30000000),
                'townhouse' => fake()->numberBetween(1500000, 15000000),
                'commercial' => fake()->numberBetween(300000, 20000000),
            },
            'rent' => match ($propertyType) {
                'apartment' => fake()->numberBetween(3000, 50000),
                'villa' => fake()->numberBetween(15000, 150000),
                'townhouse' => fake()->numberBetween(10000, 80000),
                'commercial' => fake()->numberBetween(2000, 100000),
            },
        };

        $amenityCount = fake()->numberBetween(3, 8);
        $listingAmenities = fake()->randomElements(self::$amenities, $amenityCount);

        $imageCount = fake()->numberBetween(1, 5);
        $images = [];
        $seed = fake()->randomNumber(5);
        for ($i = 1; $i <= $imageCount; $i++) {
            $images[] = "https://picsum.photos/seed/{$seed}{$i}/800/600";
        }

        $titles = [
            'apartment' => [
                'Spacious Apartment in {district}',
                'Modern Apartment with View in {district}',
                'Luxury Apartment for {type} in {district}',
                'Cozy Apartment in the Heart of {city}',
                'Fully Furnished Apartment in {district}',
            ],
            'villa' => [
                'Stunning Villa in {district}',
                'Private Villa with Garden in {district}',
                'Elegant Villa for {type} in {city}',
                'Luxury Villa with Pool in {district}',
                'Modern Villa in {district}, {city}',
            ],
            'townhouse' => [
                'Charming Townhouse in {district}',
                'Modern Townhouse in {district}',
                'Spacious Townhouse for {type} in {city}',
            ],
            'commercial' => [
                'Prime Commercial Space in {district}',
                'Office Space for Rent in {district}',
                'Shop for {type} in {city}',
                'Retail Space in {district}, {city}',
            ],
        ];

        $titleTemplate = fake()->randomElement($titles[$propertyType]);
        $title = str_replace(
            ['{district}', '{city}', '{type}'],
            [$district, $city, $listingType === 'sale' ? 'Sale' : 'Rent'],
            $titleTemplate
        );

        return [
            'title' => $title,
            'description' => fake()->paragraphs(fake()->numberBetween(2, 4), true),
            'price' => $price,
            'area_sqm' => $areaSqm,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'property_type' => $propertyType,
            'listing_type' => $listingType,
            'city' => $city,
            'district' => $district,
            'address' => fake()->streetAddress().', '.$district.', '.$city,
            'latitude' => fake()->latitude(22, 32),
            'longitude' => fake()->longitude(25, 36),
            'contact_phone' => '01'.fake()->numberBetween(0, 2).fake()->numerify('########'),
            'contact_whatsapp' => '01'.fake()->numberBetween(0, 2).fake()->numerify('########'),
            'is_featured' => fake()->boolean(20),
            'is_active' => fake()->boolean(90),
            'images' => $images,
            'amenities' => $listingAmenities,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
        ]);
    }

    public function forSale(): static
    {
        return $this->state(fn () => [
            'listing_type' => 'sale',
        ]);
    }

    public function forRent(): static
    {
        return $this->state(fn () => [
            'listing_type' => 'rent',
        ]);
    }
}
