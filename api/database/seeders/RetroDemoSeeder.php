<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RetroDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Os dados de demonstração são reservados ao ambiente local.');

            return;
        }

        foreach (['consoles', 'jogos', 'acessorios'] as $slug) {
            Storage::disk('public')->put("retro-demo/{$slug}.svg", file_get_contents(resource_path("retro-media/{$slug}.svg")));
        }

        $demoGuestId = DB::transaction(function () {
            $this->seedCatalog();
            $users = $this->resolve(DemoUserSeeder::class)->run();

            $dreamcast = Product::query()->where('name', 'Sega Dreamcast')->first();
            $vmu = Product::query()->where('name', 'Visual Memory Unit')->first();
            $demoOrderProduct = Product::query()->where('name', 'Master System III')->first();

            if ($dreamcast !== null && $dreamcast->is_active && $dreamcast->stock > 0) {
                $customerCart = Cart::query()->firstOrCreate(['user_id' => $users['ana']->id]);
                $customerCart->items()->firstOrCreate(
                    ['product_id' => $dreamcast->id],
                    ['quantity' => min(2, $dreamcast->stock)],
                );
            }

            if ($demoOrderProduct !== null) {
                $this->seedOrders($users['bruno'], $demoOrderProduct);
            }

            $guestId = Cache::get('retro-demo.guest-cart');
            $guest = $guestId ? Cart::query()->whereNull('user_id')->find($guestId) : null;
            if ($guest === null && $vmu !== null && $vmu->is_active && $vmu->stock > 0) {
                $guest = Cart::query()->create();
                $guest->items()->create(['product_id' => $vmu->id, 'quantity' => 1]);
            }

            return $guest?->id;
        });

        $this->call(RetroProductImageSeeder::class);

        if ($demoGuestId !== null) {
            Cache::forever('retro-demo.guest-cart', $demoGuestId);
            $this->command?->info('Carrinho de visitante de demonstração criado com ID '.$demoGuestId.'.');
        }
    }

    private function seedCatalog(): void
    {
        $catalog = [
            'consoles' => ['Consoles', [
                ['Sega Dreamcast', 79990, 3, 'Console com controle e cabos para explorar a biblioteca da Sega.'],
                ['Master System III', 39990, 12, 'Console de 8 bits para revisitar os clássicos da Sega.'],
                ['Super Nintendo', 69990, 6, 'Uma porta de entrada para a era dos 16 bits.'],
                ['Mega Drive III', 54990, 5, 'Console para cartuchos da família Mega Drive.'],
                ['Nintendo 64', 84990, 4, 'Console para reviver aventuras da geração 3D.'],
                ['PlayStation', 45990, 6, 'O primeiro PlayStation, com controle e cabos.'],
                ['Nintendo GameCube', 109990, 4, 'Console compacto com controle e cabos.'],
                ['Game Boy Color', 59990, 5, 'Portátil de cartuchos com tela colorida.'],
                ['Game Boy Advance SP', 67990, 7, 'Portátil dobrável com carregador.'],
                ['PlayStation 2 Slim', 64990, 2, 'Console compacto da linha PlayStation 2.'],
            ]],
            'jogos' => ['Jogos', [
                ['Sonic Adventure', 14990, 0, 'Aventura em 3D com Sonic e seus amigos no Dreamcast.'],
                ['Crazy Taxi', 11990, 7, 'Corridas urbanas com passageiros e tempo contado.'],
                ['Jet Set Radio', 22990, 5, 'Patins, grafite e música em uma cidade estilizada.'],
                ['Skies of Arcadia', 41990, 3, 'RPG com piratas do céu e exploração de ilhas flutuantes.'],
                ['Shenmue', 26990, 4, 'Aventura narrativa com investigação e exploração.'],
                ['Super Mario World', 14990, 8, 'Plataforma em cartucho para Super Nintendo.'],
                ['Donkey Kong Country', 15990, 4, 'Plataforma com fases na selva e trilha marcante.'],
                ['Street Fighter II', 11990, 9, 'Jogo de luta para revisitar partidas de fliperama.'],
                ['Mario Kart 64', 21990, 5, 'Corridas de kart para Nintendo 64.'],
                ['Sonic the Hedgehog 2', 10990, 8, 'Plataforma veloz para Mega Drive.'],
            ]],
            'acessorios' => ['Acessórios', [
                ['Visual Memory Unit', 9990, 5, 'Cartão de memória VMU para Dreamcast.'],
                ['Controle Dreamcast', 13990, 8, 'Controle com slots para acessórios do Dreamcast.'],
                ['Vibration Pack Dreamcast', 8990, 5, 'Módulo de vibração para controle compatível.'],
                ['Controle Super Nintendo', 8990, 9, 'Controle com cabo para Super Nintendo.'],
                ['Controle Mega Drive 6 botões', 9990, 6, 'Controle com seis botões frontais para Mega Drive.'],
                ['Memory Card PlayStation', 4990, 14, 'Cartão de memória para jogos do primeiro PlayStation.'],
                ['Memory Card GameCube', 6990, 7, 'Cartão de memória para Nintendo GameCube.'],
                ['Cabo AV Nintendo', 3990, 16, 'Cabo de áudio e vídeo composto para modelos compatíveis.'],
                ['Adaptador USB de controle', 7990, 8, 'Adaptador para conectar um controle compatível a USB.'],
                ['Estojo para cartuchos', 4990, 10, 'Estojo para organizar cartuchos portáteis.'],
            ]],
        ];

        $shelves = [];
        foreach ($catalog as $slug => [$label, $rows]) {
            $category = Category::query()->firstOrCreate(['slug' => $slug], ['name' => $label]);

            foreach ($rows as $position => $row) {
                $shelves[$position][] = [$category->id, $slug, $row];
            }
        }

        // Alterna categorias na ordenação de produtos mais recentes.
        krsort($shelves);
        foreach ($shelves as $shelf) {
            foreach (array_reverse($shelf) as [$categoryId, $slug, [$name, $price, $stock, $description]]) {
                $placeholder = "retro-demo/{$slug}.svg";
                $placeholderUrl = Product::storagePhotoUrl($placeholder);
                $product = Product::withTrashed()->firstOrCreate(
                    ['category_id' => $categoryId, 'name' => $name],
                    [
                        'description' => $description,
                        'price_cents' => $price,
                        'stock' => $stock,
                        'is_active' => $name !== 'PlayStation 2 Slim',
                        'image_path' => $placeholder,
                        'photo_url' => $placeholderUrl,
                    ],
                );

                if ($product->image_path === null && $product->photo_url === null) {
                    $product->update(['image_path' => $placeholder, 'photo_url' => $placeholderUrl]);
                }
            }
        }
    }

    private function seedOrders(User $user, Product $product): void
    {
        $address = $user->addresses()->firstOrCreate(['recipient' => $user->name], [
            'postal_code' => '01001-000',
            'street' => 'Rua de Demonstração',
            'number' => '16',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        foreach (Order::STATUSES as $status) {
            if ($user->orders()->where('status', $status)->exists()) {
                continue;
            }

            if ($status !== Order::CANCELLED && $product->stock < 1) {
                continue;
            }

            $price = $product->price_cents;
            $order = $user->orders()->create([
                'status' => $status,
                'shipping_address' => $address->only(Address::FIELDS),
                'subtotal_cents' => $price,
                'shipping_cents' => CheckoutService::SHIPPING_CENTS,
                'total_cents' => $price + CheckoutService::SHIPPING_CENTS,
                'paid_at' => in_array($status, [Order::PAID, Order::SHIPPED, Order::DELIVERED], true) ? now() : null,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 1,
                'unit_price_cents' => $price,
                'subtotal_cents' => $price,
            ]);

            if ($status !== Order::CANCELLED) {
                $product->decrement('stock');
            }
        }
    }
}
