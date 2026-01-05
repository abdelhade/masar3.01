<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Modules\HR\Models\ContractType;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    public function run(): void
    {
        $contractTypes = [
            [
                'name' => 'عقد عمل',
                'description' => 'عقد عمل',
            ],
            [
                'name' => 'عقد تدريب',
                'description' => 'عقد تدريب',
            ],
        ];

        foreach ($contractTypes as $contractType) {
            ContractType::firstOrCreate(
                ['name' => $contractType['name']],
                $contractType
            );
        }
    }
}
