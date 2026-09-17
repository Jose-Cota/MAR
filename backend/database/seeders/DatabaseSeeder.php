<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PeiSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
        
        // Ensure Admin user has role (this is handled in the RolesAndPermissionsSeeder now,
        // but just in case, we leave the factory call if we need to create it for testing)
        // User::factory()->create([...])
    }
}
