<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('ADMIN_SEED_PASSWORD');

        if (empty($password)) {
            $this->command?->warn('ADMIN_SEED_PASSWORD belum diset di .env. UserSeeder dilewati demi keamanan.');
            return;
        }

        $user = User::where('name', 'admin')->first();
        if ($user) {
            $user->email = env('ADMIN_SEED_EMAIL', 'admin@bpkad.com');
            $user->password = Hash::make($password);
            $user->save();
        } else {
            User::create([
                'name' => 'admin',
                'email' => env('ADMIN_SEED_EMAIL', 'admin@bpkad.com'),
                'password' => Hash::make($password),
            ]);
        }
    }
}
