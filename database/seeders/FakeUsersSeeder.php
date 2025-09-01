<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class FakeUsersSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * Command: php artisan db:seed --class=FakeUsersSeeder
     */
    public function run(): void
    {
        User::factory()->count(500)->create();
    }
}
