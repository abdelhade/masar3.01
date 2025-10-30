<?php

namespace Modules\Inquiries\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Models\Permission;
use Modules\Authorization\Models\Role;

class InquiriesPermissionsSeeder extends Seeder
{
    // public function run(): void
    // {



    //     // Clear permission cache
    //     app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    //     // === 1. تعريف الصلاحيات باللغة العربية ===
    //     $groupedPermissions = [
    //         'الاستفسارات' => [
    //             'الاستفسار',             // (base) سيتم إنشاء: عرض الاستفسار, إضافة الاستفسار...
    //             'تفاصيل الاستفسار',     // (standalone)
    //             'التقييم والدرجة',      // (standalone)
    //             'التعليق',             // (base)
    //             'المرفق',              // (base)
    //             'التصدير',             // (base)
    //             'التقرير',              // (base)
    //         ],
    //         'أصحاب المصلحة (استفسارات)' => [ // تمييزاً لها عن البيانات الأساسية
    //             'عملاء الاستفسارات',    // (base)
    //             'المقاولون الرئيسيون', // (base)
    //             'الاستشاريون',          // (base)
    //             'الملاك',               // (base)
    //         ],
    //         'بيانات الاستفسارات الاساسية' => [
    //             'أحجام المشاريع',           // (standalone)
    //             'أولوية KON',             // (standalone)
    //             'أولوية العميل',        // (standalone)
    //             'مصادر الاستفسار',     // (standalone)
    //             'تصنيفات العمل',      // (standalone)
    //         ],
    //         'المطلوبات والشروط (استفسارات)' => [
    //             'قائمة المطلوب تقديمها', // (standalone)
    //             'شروط العمل',           // (standalone)
    //         ],
    //         'مستندات الاستفسارات' => [
    //             'مستندات المشروع',       // (standalone)
    //         ],
    //         'تقييم الاستفسارات' => [
    //             'حساب الدرجة',          // (standalone)
    //             'مستوى الصعوبة',       // (standalone)
    //         ],
    //     ];

    //     // === 2. تعريف الأفعال باللغة العربية (لتطابق الواجهة) ===
    //     $actions = ['عرض', 'إضافة', 'تعديل', 'حذف', 'طباعة'];

    //     foreach ($groupedPermissions as $category => $items) {
    //         foreach ($items as $base) {
    //             // نفس اللوجيك: التحقق من المسافة
    //             if (str_contains($base, ' ') || in_array($category, ['بيانات الاستفسارات الاساسية', 'المطلوبات والشروط (استفسارات)', 'مستندات الاستفسارات', 'تقييم الاستفسارات'])) {
    //                 // (standalone) - صلاحية قائمة بذاتها (أو كل ما في الفئات الأساسية)
    //                 Permission::firstOrCreate(
    //                     ['name' => $base, 'guard_name' => 'web'],
    //                     ['category' => $category]
    //                 );
    //             } else {
    //                 // (base) - إضافة الأفعال
    //                 foreach ($actions as $action) {
    //                     $name = "$action $base";
    //                     Permission::firstOrCreate(
    //                         ['name' => $name, 'guard_name' => 'web'],
    //                         ['category' => $category]
    //                     );
    //                 }
    //             }
    //         }
    //     }

    //     // صلاحية خاصة لتغيير الحالة
    //     Permission::firstOrCreate(
    //         ['name' => 'تغيير حالة الاستفسار', 'guard_name' => 'web'],
    //         ['category' => 'الاستفسارات']
    //     );


    //     // === 3. إسناد الأدوار بالأسماء العربية ===

    //     // دور "admin" من المفترض أنه موجود مسبقاً
    //     $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    //     $admin->syncPermissions(Permission::all()); // سيعطيه كل الصلاحيات الجديدة أيضاً

    //     $estimator = Role::firstOrCreate(['name' => 'estimator', 'guard_name' => 'web']);
    //     $estimator->syncPermissions([
    //         'عرض الاستفسار',
    //         'إضافة الاستفسار',
    //         'تعديل الاستفسار',
    //         'تفاصيل الاستفسار',
    //         'تغيير حالة الاستفسار',
    //         'عرض التعليق',
    //         'إضافة التعليق',
    //         'تعديل التعليق',
    //         'حذف التعليق',
    //         'عرض المرفق',
    //         'إضافة المرفق',
    //         'حذف المرفق',
    //     ]);

    //     $manager = Role::firstOrCreate(['name' => 'project manager', 'guard_name' => 'web']);
    //     $manager->syncPermissions([
    //         'عرض الاستفسار',
    //         'تعديل الاستفسار',
    //         'عرض التقرير',
    //     ]);
    // }
}
