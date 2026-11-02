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
        $user = User::where('name', 'admin')->first();
        if ($user) {
            $user->email = 'admin@bpkad.com';
            $user->password = Hash::make('12345');
            $user->save();
        } else {
            User::create([
                'name' => 'admin',
                'email' => 'admin@bpkad.com',
                'password' => Hash::make('12345'),
            ]);
        }
    }
}
