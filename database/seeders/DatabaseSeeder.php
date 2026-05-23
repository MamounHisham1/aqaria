<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@aqaria.com',
            'is_admin' => true,
        ]);

        $this->seedListings();
    }

    private function seedListings(): void
    {
        // Create 8 featured listings across different cities and types
        Listing::factory()->count(2)->featured()->forSale()->create();
        Listing::factory()->count(2)->featured()->forRent()->create();
        Listing::factory()->count(2)->featured()->create(['property_type' => 'villa']);
        Listing::factory()->count(2)->featured()->create();

        // Create the remaining random listings
        Listing::factory()->count(42)->create();
    }
}
