<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('categories')->upsert(
            [
                ['name' => 'Consoles', 'slug' => 'consoles', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Jogos', 'slug' => 'jogos', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Acessórios', 'slug' => 'acessorios', 'created_at' => $now, 'updated_at' => $now],
            ],
            ['slug'],
            ['name', 'updated_at'],
        );
    }
}