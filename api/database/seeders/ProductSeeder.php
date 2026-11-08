<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products =   [
            [
                'category' => 'consoles',
                'name' => 'Sega Dreamcast',
                'description' => 'Console Sega Dreamcast com um controle.',
                'price_cents' => 79990,
                'stock' => 3,
                'is_active' => true,
            ],
            [
                'category' => 'consoles',
                'name' => 'Playstation 2',
                'description' => 'Console Playstation 2 com um controle.',
                'price_cents' => 49990,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Sonic Adventure',
                'description' => 'Jogo Sonic Adventure para Sega Dreamcast.',
                'price_cents' => 19990,
                'stock' => 10,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Final Fantasy X',
                'description' => 'Jogo Final Fantasy X para Playstation 2.',
                'price_cents' => 29990,
                'stock' => 7,
                'is_active' => true,
            ],
            [
                'category' => 'acessorios',
                'name' => 'Controle Sega Saturn',
                'description' => 'Controle original para o console Sega Saturn.',
                'price_cents' => 14990,
                'stock' => 4,
                'is_active' => true,
            ],
            [
                'category' => 'acessorios',
                'name' => 'Memory Card Playstation 2',
                'description' => 'Memory Card original para o console Playstation 2.',
                'price_cents' => 9990,
                'stock' => 6,
                'is_active' => true,
            ],
            [
                'category' => 'acessorios',
                'name' => 'Controle Dreamcast',
                'description' => 'Controle original para o console Sega Dreamcast.',
                'price_cents' => 12990,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'category' => 'consoles',
                'name' => 'Nintendo 64',
                'description' => 'Console Nintendo 64 com um controle.',
                'price_cents' => 39990,
                'stock' => 2,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Super Mario 64',
                'description' => 'Jogo Super Mario 64 para Nintendo 64.',
                'price_cents' => 24990,
                'stock' => 8,
                'is_active' => true,
            ],
            [
                'category' => 'acessorios',
                'name' => 'Controle Nintendo 64',
                'description' => 'Controle original para o console Nintendo 64.',
                'price_cents' => 13990,
                'stock' => 3,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'The Legend of Zelda: Ocarina of Time',
                'description' => 'Jogo The Legend of Zelda: Ocarina of Time para Nintendo 64.',
                'price_cents' => 29990,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'GoldenEye 007',
                'description' => 'Jogo GoldenEye 007 para Nintendo 64.',
                'price_cents' => 19990,
                'stock' => 6,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Mario Kart 64',
                'description' => 'Jogo Mario Kart 64 para Nintendo 64.',
                'price_cents' => 24990,
                'stock' => 4,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Donkey Kong 64',
                'description' => 'Jogo Donkey Kong 64 para Nintendo 64.',
                'price_cents' => 19990,
                'stock' => 3,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Resident Evil Code: Veronica',
                'description' => 'Jogo Resident Evil Code: Veronica para Dreamcast.',
                'price_cents' => 29990,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Metal Gear Solid 2: Sons of Liberty',
                'description' => 'Jogo Metal Gear Solid 2: Sons of Liberty para Playstation 2.',
                'price_cents' => 39990,
                'stock' => 4,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Soulcalibur',
                'description' => 'Jogo Soulcalibur para Dreamcast.',
                'price_cents' => 24990,
                'stock' => 6,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Tekken 5',
                'description' => 'Jogo Tekken 5 para Playstation 2.',
                'price_cents' => 19990,
                'stock' => 7,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'F-Zero X',
                'description' => 'Jogo F-Zero X para Nintendo 64.',
                'price_cents' => 24990,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'category' => 'jogos',
                'name' => 'Star Fox 64',
                'description' => 'Jogo Star Fox 64 para Nintendo 64.',
                'price_cents' => 19990,
                'stock' => 4,
                'is_active' => true,
            ],
        ];


        foreach ($products as $product) {
            $category = Category::query()
                ->where('slug', $product['category'])
                ->firstOrFail();

            Product::query()->firstOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $product['name'],
                ],
                [
                    'description' => $product['description'],
                    'price_cents' => $product['price_cents'],
                    'stock' => $product['stock'],
                    'is_active' => $product['is_active'],
                ],
            );
        }
    }
}