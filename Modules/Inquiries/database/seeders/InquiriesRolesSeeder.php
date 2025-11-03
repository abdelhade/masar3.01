<?php

namespace Modules\Inquiries\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Inquiries\Models\InquirieRole;

class InquiriesRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Client',
                'description' => 'Project Client or Customer'
            ],
            [
                'name' => 'Main Contractor',
                'description' => 'Main Contractor for the Project'
            ],
            [
                'name' => 'Consultant',
                'description' => 'Project Consultant or Advisory'
            ],
            [
                'name' => 'Owner',
                'description' => 'Project Owner'
            ],
            [
                'name' => 'Engineer',
                'description' => 'Assigned Engineer for the Project'
            ],
        ];

        foreach ($roles as $role) {
            InquirieRole::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
