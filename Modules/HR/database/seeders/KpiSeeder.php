<?php

declare(strict_types=1);

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HR\Models\Kpi;

class KpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kpis = [
            [
                'name' => 'الأداء الوظيفي',
                'description' => 'جودة تنفيذ المهام حسب الوصف الوظيفي',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الإنتاجية',
                'description' => 'الكمية المنجزة مقارنة بالأهداف',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الدقة والأنتباه للتفاصيل',
                'description' => 'تقليل الأخطاء والانتباه للجودة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الكفاءة الفنية',
                'description' => 'استخدام الأدوات والتقنيات المرتبطة بالوظيفة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'التواصل',
                'description' => 'شفهي وكتابي داخل الفريق ومع العملاء',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'التعاون',
                'description' => 'تقبل الآراء، دعم الفريق، التحول',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الالتزام والانضباط',
                'description' => 'الحضور، الالتزام بالمواعيد والسياسات',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'المبادرة',
                'description' => 'السعي لتنفيذ حلول دون انتظار الأوامر',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الابتكار',
                'description' => 'التفكير الإبداعي وتقديم أفكار جديدة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'التكيف والمرونة',
                'description' => 'التفاعل الإيجابي مع التغيير أو الضغط',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'خدمة العملاء',
                'description' => 'التعامل الفني، حل المشاكل، رضا العملاء',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'إدارة الوقت',
                'description' => 'تنظيم الوقت وتنفيذ المهام في موعدها',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'القيادة',
                'description' => 'قيادة الفريق، التوجيه، تحفيز الآخرين',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'التطوير الذاتي',
                'description' => 'السعي لتعلم مهارات جديدة وتحسين الأداء',
            ],
        ];

        foreach ($kpis as $kpi) {
            Kpi::firstOrCreate( // Changed from \App\Models\Kpi to Kpi
                ['name' => $kpi['name']],
                $kpi
            );
        }
    }
}
