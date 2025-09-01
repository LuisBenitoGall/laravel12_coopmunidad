<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder{
    /**
     * Seed the application's database.
     */
    public function run(): void{
        //Mantener el orden de ejecución de los seeds:
        $this->call(AccountsTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(ProvincesTableSeeder::class);
        $this->call(TownsTableSeeder::class);
        $this->call(ModulesTableSeeder::class);
        $this->call(FunctionalitiesTableSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UsersTableSeeder::class);

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
         
        //$this->call(FakeUsersSeeder::class);
    }
}
