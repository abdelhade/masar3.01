<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $total = 100000;
        $batchSize = 1000;

        for ($i = 0; $i < $total / $batchSize; $i++) {
            Test::factory()->count($batchSize)->create();
        }
    }
}
