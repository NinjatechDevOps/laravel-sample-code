<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $user = User::create([
        //     'first_name' => 'Admin',
        //     'last_name' => 'BDE',
        //     'email' => 'admin@admin.com',
        //     'password' => bcrypt('admin@admin.com'),
        //     'is_active' => true,
        // ]);
        // /** @var Role $role */
        // $role = Role::where('name', 'Admin')->first();
        // if ($role) {
        //     $user->assignRole([$role->id]);
        // }
        $admins = [
            [
                'first_name' => 'Admin',
                'last_name' => 'BDE',
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin@admin.com'),
                'is_active' => true,
            ]
        ];
        foreach ($admins as $admin) {
            // Check if admin with the current email already exists
            $existingAdmin = User::where('email', $admin['email'])->first();
            // If no admin with the current email exists, create a new admin
            if (!$existingAdmin) {
                $newAdmin =  User::create($admin);
                // Assign the 'Admin' role to the new admin
                $role = Role::where('name', 'Admin')->first();
                if ($role) {
                    $newAdmin->assignRole([$role->id]);
                }
                echo "Admin with email '{$admin['email']}' has been created.";
            } else {
                echo "Admin with email '{$admin['email']}' already exists.";
            }
        }
    }
}
