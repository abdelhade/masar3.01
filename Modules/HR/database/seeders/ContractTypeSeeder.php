<?php

declare(strict_types=1);

namespace Modules\HR\Database\Seeders;

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
