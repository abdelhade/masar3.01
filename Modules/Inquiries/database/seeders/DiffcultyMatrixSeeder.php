<?php

namespace Modules\Inquiries\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiffcultyMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Safety level',
                'score' => 3,
                'options' => json_encode([
                    'Normal' => 1,
                    'Medium' => 2,
                    'High' => 3,
                ]),
            ],
            ['name' => 'Vendor list', 'score' => 1, 'options' => null],
            ['name' => 'Consultant approval', 'score' => 1, 'options' => null],
            ['name' => 'Machines approval', 'score' => 1, 'options' => null],
            ['name' => 'Labours approval', 'score' => 1, 'options' => null],
            ['name' => 'Security approvals', 'score' => 1, 'options' => null],
            [
                'name' => 'Working Hours',
                'score' => 3,
                'options' => json_encode([
                    'Normal(10hr/6 days)' => 1,
                    'Half week(8hr, 4day)' => 2,
                    'Half day(4hr/6days)' => 2,
                    'Half week-Half day(4hr/4day)' => 3,
                ]),
            ],
            ['name' => 'Night shift required', 'score' => 1, 'options' => null],
            ['name' => 'Tight time schedule', 'score' => 1, 'options' => null],
            ['name' => 'Remote Location', 'score' => 1, 'options' => null],
            ['name' => 'Difficult Access Site', 'score' => 1, 'options' => null],
            ['name' => 'Without advance payment', 'score' => 1, 'options' => null],
            [
                'name' => 'Payment conditions',
                'score' => 2,
                'options' => json_encode([
                    'CDC' => 0,
                    'PDC 30 days' => 1,
                    'PDC 90 days' => 2,
                ]),
            ],
            [
                'name' => 'Concrete station availability',
                'score' => 2,
                'options' => json_encode([
                    'Near' => 1,
                    'Far distance' => 2,
                ]),
            ],
        ];


        DB::table('work_conditions')->insert($items);

        $data = [
            ['name' => 'Pre qualification', 'score' => 0],
            ['name' => 'Design', 'score' => 1],
            ['name' => 'MOS', 'score' => 0],
            ['name' => 'Material Submittal', 'score' => 1],
            ['name' => 'Methodology', 'score' => 1],
            ['name' => 'Time schedule', 'score' => 1],
            ['name' => 'Insurances', 'score' => 1],
            ['name' => 'Project team', 'score' => 1],
        ];

        DB::table('submittal_checklists')->insert($data);
    }
}
