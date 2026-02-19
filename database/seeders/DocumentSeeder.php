<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\User;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $titles = [
            "HR Policy Update",
            "Finance Monthly Report",
            "IT Security Guidelines",
            "Marketing Campaign Plan",
            "Operations Checklist",
            "Employee Handbook",
            "System Architecture",
            "Budget Proposal",
            "Training Manual",
            "Project Timeline",
        ];

        $accessLevels = [
            'public','public','public','public','public',
            'department','department','department',
            'private','private'
        ];

        for ($i = 1; $i <= 30; $i++) {

            $user = $users->random();
            $access = $accessLevels[array_rand($accessLevels)];

            Document::create([
                'title' => $titles[array_rand($titles)] . " {$i}",
                'description' => "Sample seeded document {$i}",
                'category_id' => rand(1,3),
                'department_id' => $access === 'public'
                    ? null
                    : $user->department_id,
                'access_level' => $access,
                'file_name' => "dummy{$i}.pdf",
                'file_path' => "documents/dummy{$i}.pdf",
                'file_size' => rand(10000, 90000),
                'file_type' => "application/pdf",
                'uploaded_by' => $user->id,
                'download_count' => rand(0,20),
            ]);
        }
    }
}