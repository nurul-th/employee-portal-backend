<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;    
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        //---Admin---
        $admin = user::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $admin->assignRole('Admin');

        //---Manager Department 1---
        $manager1 = user::firstOrCreate(
            ['email' => 'manager1@example.com'],
            [   'name' => 'Manager Dept 1',
                'password' => Hash::make('password'),
                'department_id' => 1, 
            ]
        );
        $manager1->assignRole('Manager');

        //---Manager Department 2---
        $manager2 = user::firstOrCreate(
            ['email' => 'manager2@example.com'],
            [   'name' => 'Manager Dept 2',
                'password' => Hash::make('password'),
                'department_id' => 2,
            ]
        );
        $manager2->assignRole('Manager');

        //---Employee 1---
        $employee1 = user::firstOrCreate (
            ['email' => 'employee1@example.com'],
            [
                'name' => 'Employee Dept 1',
                'password' => Hash::make('password'),
                'department_id' => 1,
            ]
        );
        $employee1 -> assignRole('Employee');

        //---Employee 2---
        $employee2 = user::firstOrCreate (
            ['email' => 'employee2@example.com'],
            [
                'name' => 'Employee Dept 2',
                'password' => Hash::make('password'),
                'department_id' => 2,
            ]
        );
        $employee2 -> assignRole('Employee');
    }
}