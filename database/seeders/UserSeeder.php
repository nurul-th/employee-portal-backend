<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ADMIN =====
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $admin->assignRole('Admin');

        // ===== MANAGERS (4) =====
        for ($i = 1; $i <= 4; $i++) {
            $manager = User::firstOrCreate(
                ['email' => "manager{$i}@example.com"],
                [
                    'name' => "Manager Dept {$i}",
                    'password' => Hash::make('password'),
                    'department_id' => $i,
                ]
            );

            $manager->assignRole('Manager');
        }

        // ===== EMPLOYEES (7) =====
        $departments = [1,2,3,4,5,1,2];

        foreach ($departments as $index => $dept) {
            $num = $index + 1;

            $employee = User::firstOrCreate(
                ['email' => "employee{$num}@example.com"],
                [
                    'name' => "Employee {$num}",
                    'password' => Hash::make('password'),
                    'department_id' => $dept,
                ]
            );

            $employee->assignRole('Employee');
        }
    }
}
