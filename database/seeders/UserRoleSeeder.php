<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // --- Admin ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $admin->assignRole('Admin');

        // --- Demo Manager (for README) ---
        $managerDemo = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $managerDemo->assignRole('Manager');

        // --- Demo Employee (for README) ---
        $employeeDemo = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $employeeDemo->assignRole('Employee');

        // --- Manager Department 1 ---
        $manager1 = User::firstOrCreate(
            ['email' => 'manager1@example.com'],
            [
                'name' => 'Manager Dept 1',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $manager1->assignRole('Manager');

        // --- Manager Department 2 ---
        $manager2 = User::firstOrCreate(
            ['email' => 'manager2@example.com'],
            [
                'name' => 'Manager Dept 2',
                'password' => Hash::make('password'),
                'department_id' => 2,
            ]
        );
        $manager2->assignRole('Manager');

        // --- Employee 1 ---
        $employee1 = User::firstOrCreate(
            ['email' => 'employee1@example.com'],
            [
                'name' => 'Employee Dept 1',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $employee1->assignRole('Employee');

        // --- Employee 2 ---
        $employee2 = User::firstOrCreate(
            ['email' => 'employee2@example.com'],
            [
                'name' => 'Employee Dept 2',
                'password' => Hash::make('password'),
                'department_id' => 2,
            ]
        );
        $employee2->assignRole('Employee');
    }
}