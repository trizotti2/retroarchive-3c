<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * @return array<string, User>
     */
    public function run(): array
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('As contas de demonstração são reservadas ao ambiente local.');

            return [];
        }

        $accounts = [
            'ana' => ['Ana Demo', 'customer'],
            'bruno' => ['Bruno Demo', 'customer'],
            'carla' => ['Carla Demo', 'customer'],
            'admin' => ['Admin Demo', 'admin'],
        ];
        $users = [];

        foreach ($accounts as $key => [$name, $role]) {
            $users[$key] = User::query()->firstOrCreate(
                ['email' => "{$key}@retro.example"],
                ['name' => $name, 'role' => $role, 'password' => 'RetroDemo2026!'],
            );
        }

        return $users;
    }
}
