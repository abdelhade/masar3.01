<?php

declare(strict_types=1);

namespace Modules\HR\Database\Seeders;

use Modules\HR\Models\EmployeesJob;
use Illuminate\Database\Seeder;

class EmployeesJobSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Accountant',
                'description' => 'Accountant',
            ],
            [
                'title' => 'HR Manager',
                'description' => 'HR Manager',
            ],
            [
                'title' => 'IT Manager',
                'description' => 'IT Manager',
            ],
            [
                'title' => 'Marketing Manager',
                'description' => 'Marketing Manager',
            ],
            [
                'title' => 'Sales Manager',
                'description' => 'Sales Manager',
            ],
            [
                'title' => 'Customer Service Manager',
                'description' => 'Customer Service Manager',
            ],
            [
                'title' => 'Project Manager',
                'description' => 'Project Manager',
            ],
            [
                'title' => 'Software Engineer',
                'description' => 'Software Engineer',
            ],
            [
                'title' => 'Network Engineer',
                'description' => 'Network Engineer',
            ],
            [
                'title' => 'Database Administrator',
                'description' => 'Database Administrator',
            ],
        ];

        foreach ($jobs as $job) {
            EmployeesJob::firstOrCreate(
                ['title' => $job['title']],
                $job
            );
        }
    }
}
