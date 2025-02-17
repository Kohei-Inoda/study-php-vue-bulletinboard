<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'ai_bot@example.com'],
            [
                'name' => 'Gemini AI',
                'password' => Hash::make('password'), // 仮パスワード
            ]
        );
    }
}