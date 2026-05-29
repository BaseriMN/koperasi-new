<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,          // 1. Cipta 7 peranan
            PermissionSeeder::class,    // 2. Cipta kebenaran & petakan ke peranan
            SuperUserSeeder::class,     // 3. Cipta Super User (Muhamad Baseri)
            ModuleAccessSeeder::class,  // 4. Akses modul lalai bagi setiap peranan
            //ModuleSeeder::class,        // 5. Cipta modul
        ]);
    }
}
