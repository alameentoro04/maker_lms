<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Creates one Super Admin so the platform is never launched with no way in.
     * Change this password immediately after first login in any real environment.
     */
    public function run(): void
    {
        $superAdminRole = Role::query()->where('slug', Role::SUPER_ADMIN)->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => 'admin@makers.al-ismail.com.ng'],
            [
                'role_id' => $superAdminRole->id,
                'name' => 'Al-Ismail Admin',
                'password' => Hash::make('ChangeMe!12345'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
    }
}
