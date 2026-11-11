<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class RetroProductImageSeeder extends Seeder
{
    private const PHOTOS = [
        'consoles' => [
            'Sega Dreamcast' => 'sega-dreamcast.jpg',
            'Master System III' => 'master-system-iii.png',
            'Super Nintendo' => 'super-nintendo.jpg',
            'Nintendo 64' => 'nintendo-64.jpg',
            'PlayStation' => 'playstation.jpg',
            'Nintendo GameCube' => 'nintendo-gamecube.jpg',
            'Game Boy Color' => 'game-boy-color.jpg',
            'Game Boy Advance SP' => 'game-boy-advance-sp.jpg',
            'PlayStation 2 Slim' => 'playstation-2-slim.jpg',
        ],
        'jogos' => [
            'Mario Kart 64' => 'mario-kart-64.jpg',
        ],
        'acessorios' => [
            'Visual Memory Unit' => 'visual-memory-unit.jpg',
            'Controle Dreamcast' => 'controle-dreamcast.jpg',
            'Vibration Pack Dreamcast' => 'vibration-pack-dreamcast.jpg',
            'Controle Super Nintendo' => 'controle-super-nintendo.jpg',
            'Controle Mega Drive 6 botões' => 'controle-mega-drive-6-botoes.jpg',
            'Memory Card PlayStation' => 'memory-card-playstation.jpg',
            'Memory Card GameCube' => 'memory-card-gamecube.png',
            'Cabo AV Nintendo' => 'cabo-av-nintendo.jpg',
            'Estojo para cartuchos' => 'estojo-para-cartuchos.jpg',
        ],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');
        $templateHashes = json_decode(
            file_get_contents(resource_path('retro-media/placeholders/original-photo-templates.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $updated = 0;

        foreach (self::PHOTOS as $categorySlug => $photos) {
            $categoryId = Category::query()->where('slug', $categorySlug)->value('id');

            if ($categoryId === null) {
                continue;
            }

            $products = Product::query()
                ->where('category_id', $categoryId)
                ->get(['id', 'name', 'image_path', 'photo_url']);

            foreach ($products as $product) {
                $template = 'placeholders/'.$categorySlug.'/'.Str::slug($product->name).'.png';
                $templateSource = resource_path('retro-media/'.$template);
                $hasTemplate = is_file($templateSource);
                $original = isset($photos[$product->name]) ? "products/{$categorySlug}/".$photos[$product->name] : null;
                if (! $hasTemplate && $original === null) {
                    continue;
                }

                $demoPaths = ["retro-demo/{$categorySlug}.svg", "retro-demo/{$template}"];
                if ($original !== null) {
                    $demoPaths[] = "retro-demo/{$original}";
                }
                $demoUrls = array_map(Product::storagePhotoUrl(...), $demoPaths);

                if (! in_array($product->photo_url, [null, '', ...$demoUrls], true)
                    || ! in_array($product->image_path, [null, '', ...$demoPaths], true)) {
                    continue;
                }

                $relative = $template;
                if ($original !== null && (! $hasTemplate || hash_file('sha256', $templateSource) === ($templateHashes[$template] ?? null))) {
                    $relative = $original;
                }

                $path = "retro-demo/{$relative}";
                $photoUrl = Product::storagePhotoUrl($path);
                $source = resource_path('retro-media/'.$relative);
                $bytes = is_file($source) ? file_get_contents($source) : false;
                if ($relative === $template && ($bytes === false || (@getimagesizefromstring($bytes)[2] ?? null) !== IMAGETYPE_PNG)) {
                    throw new RuntimeException("O arquivo {$relative} precisa ser uma imagem PNG válida.");
                }

                if ($bytes === false || ! $disk->put($path, $bytes)) {
                    throw new RuntimeException("Não foi possível copiar a imagem {$relative}.");
                }

                // Confere o caminho anterior para preservar uma troca feita pelo painel.
                if ($product->image_path !== $path || $product->photo_url !== $photoUrl) {
                    $updated += Product::query()
                        ->whereKey($product->id)
                        ->where('category_id', $categoryId)
                        ->where('name', $product->name)
                        ->where('image_path', $product->image_path)
                        ->where('photo_url', $product->photo_url)
                        ->update(['image_path' => $path, 'photo_url' => $photoUrl]);
                }
            }
        }

        $this->command?->info("Fotos de catálogo atualizadas: {$updated}.");
    }
}
