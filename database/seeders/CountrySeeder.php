<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      Country::insert([
        ['name' => 'Jordan'],
        ['name' => 'Saudi Arabia'],
        ['name' => 'Egypt'],
        ['name' => 'Palestine'],
    ]);
    }
}
