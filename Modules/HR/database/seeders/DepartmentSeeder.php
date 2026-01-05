<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Modules\HR\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'title' => 'IT',
                'description' => 'Information Technology Department',
            ],
            [
                'title' => 'HR',
                'description' => 'Human Resources Department',
            ],
            [
                'title' => 'Finance',
                'description' => 'Finance Department',
            ],
            [
                'title' => 'Marketing',
                'description' => 'Marketing Department',
            ],
            [
                'title' => 'Sales',
                'description' => 'Sales Department',
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['title' => $department['title']],
                $department
            );
        }
    }
}
