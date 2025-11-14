<?php

namespace Modules\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccHeadSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['id'=>1,'code'=>'1','deletable'=>0,'editable'=>0,'aname'=>'الأصول','is_stock'=>0,'is_fund'=>0,'parent_id'=>null,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>2,'code'=>'2','deletable'=>0,'editable'=>0,'aname'=>'الخصوم','is_stock'=>0,'is_fund'=>0,'parent_id'=>null,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>3,'code'=>'3','deletable'=>0,'editable'=>0,'aname'=>'حقوق الملكية','is_stock'=>0,'is_fund'=>0,'parent_id'=>null,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>4,'code'=>'4','deletable'=>0,'editable'=>0,'aname'=>'الإيرادات','is_stock'=>0,'is_fund'=>0,'parent_id'=>null,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>5,'code'=>'5','deletable'=>0,'editable'=>0,'aname'=>'المصروفات','is_stock'=>0,'is_fund'=>0,'parent_id'=>null,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>6,'code'=>'11','deletable'=>0,'editable'=>0,'aname'=>'الأصول المتداولة','is_stock'=>0,'is_fund'=>0,'parent_id'=>1,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>7,'code'=>'12','deletable'=>0,'editable'=>0,'aname'=>'الأصول الثابتة','is_stock'=>0,'is_fund'=>0,'parent_id'=>1,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>8,'code'=>'21','deletable'=>0,'editable'=>0,'aname'=>'الخصوم المتداولة','is_stock'=>0,'is_fund'=>0,'parent_id'=>2,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>9,'code'=>'31','deletable'=>0,'editable'=>0,'aname'=>'رأس المال ( الشركاء)','is_stock'=>0,'is_fund'=>0,'parent_id'=>3,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>10,'code'=>'21081','deletable'=>0,'editable'=>0,'aname'=>'جاري الشركاء','is_stock'=>0,'is_fund'=>0,'parent_id'=>81,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>11,'code'=>'33','deletable'=>0,'editable'=>0,'aname'=>'احتياطي نقدي','is_stock'=>0,'is_fund'=>0,'parent_id'=>3,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>12,'code'=>'34','deletable'=>0,'editable'=>0,'aname'=>'الأرباح المرحلة','is_stock'=>0,'is_fund'=>0,'parent_id'=>3,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>13,'code'=>'41','deletable'=>0,'editable'=>0,'aname'=>'صافي المبيعات','is_stock'=>1,'is_fund'=>0,'parent_id'=>4,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>14,'code'=>'42','deletable'=>0,'editable'=>0,'aname'=>'إيرادات اخري','is_stock'=>0,'is_fund'=>0,'parent_id'=>4,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>15,'code'=>'43','deletable'=>0,'editable'=>0,'aname'=>'أرباح و خسائر رأسمالية','is_stock'=>0,'is_fund'=>0,'parent_id'=>4,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>16,'code'=>'51','deletable'=>0,'editable'=>0,'aname'=>'تكلفة البضاعة المباعة','is_stock'=>1,'is_fund'=>0,'parent_id'=>5,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>17,'code'=>'521','deletable'=>0,'editable'=>0,'aname'=>'خامات للتصنيع','is_stock'=>1,'is_fund'=>0,'parent_id'=>82,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>18,'code'=>'522','deletable'=>0,'editable'=>0,'aname'=>'أجور مباشرة','is_stock'=>0,'is_fund'=>0,'parent_id'=>82,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>19,'code'=>'523','deletable'=>0,'editable'=>0,'aname'=>'تكاليف صناعية غير مباشرة','is_stock'=>0,'is_fund'=>0,'parent_id'=>82,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>20,'code'=>'55','deletable'=>0,'editable'=>0,'aname'=>'هالك المخزون','is_stock'=>0,'is_fund'=>0,'parent_id'=>5,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>21,'code'=>'56','deletable'=>0,'editable'=>0,'aname'=>'فروقات جرد المخزون','is_stock'=>0,'is_fund'=>0,'parent_id'=>5,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>22,'code'=>'53','deletable'=>0,'editable'=>0,'aname'=>'مصروفات اداريه عموميه','is_stock'=>0,'is_fund'=>0,'parent_id'=>5,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>23,'code'=>'1101','deletable'=>0,'editable'=>0,'aname'=>'الصناديق','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>24,'code'=>'1102','deletable'=>0,'editable'=>0,'aname'=>'البنوك','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>25,'code'=>'1103','deletable'=>0,'editable'=>0,'aname'=>'العملاء','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>26,'code'=>'1104','deletable'=>0,'editable'=>0,'aname'=>'المخازن','is_stock'=>1,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>27,'code'=>'1105','deletable'=>0,'editable'=>0,'aname'=>'أوراق القبض','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>28,'code'=>'1106','deletable'=>0,'editable'=>0,'aname'=>'مدينين آخرين','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>29,'code'=>'1107','deletable'=>0,'editable'=>0,'aname'=>'المصروفات المدفوعه مقدما','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>30,'code'=>'1108','deletable'=>0,'editable'=>0,'aname'=>'مراكز التشغيل','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>31,'code'=>'2107','deletable'=>0,'editable'=>0,'aname'=>'نقاط العملاء','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>32,'code'=>'1201','deletable'=>0,'editable'=>0,'aname'=>'أراضي','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>33,'code'=>'1202','deletable'=>0,'editable'=>0,'aname'=>'مباني','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>34,'code'=>'1203','deletable'=>0,'editable'=>0,'aname'=>'آلات و معدات','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>35,'code'=>'1204','deletable'=>0,'editable'=>0,'aname'=>'وسائل النقل','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>36,'code'=>'1205','deletable'=>0,'editable'=>0,'aname'=>'اثاث و مكاتب','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>37,'code'=>'1206','deletable'=>0,'editable'=>0,'aname'=>'أجهزة كمبيوتر','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>38,'code'=>'1207','deletable'=>0,'editable'=>0,'aname'=>'نظم معلومات','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>39,'code'=>'1208','deletable'=>0,'editable'=>0,'aname'=>'أصول فكرية','is_stock'=>0,'is_fund'=>0,'parent_id'=>7,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>40,'code'=>'14','deletable'=>0,'editable'=>0,'aname'=> 'مجمع إهلاك الأصول','is_stock'=>0,'is_fund'=>0,'parent_id'=>1,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>41,'code'=>'2101','deletable'=>0,'editable'=>0,'aname'=>'الموردين','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>42,'code'=>'2102','deletable'=>0,'editable'=>0,'aname'=>'رواتب واجور مستحقه','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>43,'code'=>'2103','deletable'=>0,'editable'=>0,'aname'=>'أوراق الدفع','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>44,'code'=>'2104','deletable'=>0,'editable'=>0,'aname'=>'دائنين اخرين','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>45,'code'=>'3101','deletable'=>0,'editable'=>1,'aname'=>'الشريك الرئيسي','is_stock'=>0,'is_fund'=>0,'parent_id'=>9,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>46,'code'=>'210811','deletable'=>0,'editable'=>1,'aname'=>' جاري الشريك الرئيسي','is_stock'=>0,'is_fund'=>0,'parent_id'=>10,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>47,'code'=>'4101','deletable'=>0,'editable'=>0,'aname'=>'المبيعات','is_stock'=>1,'is_fund'=>0,'parent_id'=>13,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>48,'code'=>'4102','deletable'=>0,'editable'=>0,'aname'=>'مردود المبيعات','is_stock'=>1,'is_fund'=>0,'parent_id'=>13,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>49,'code'=>'4103','deletable'=>0,'editable'=>0,'aname'=>'خصم مسموح به','is_stock'=>1,'is_fund'=>0,'parent_id'=>13,'is_basic'=>0,'employees_expensses'=>0],
       
            ['id'=>54,'code'=>'4201','deletable'=>0,'editable'=>0,'aname'=>'خصم مكتسب','is_stock'=>1,'is_fund'=>0,'parent_id'=>14,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>58,'code'=>'5221','deletable'=>0,'editable'=>1,'aname'=>'أجور عمالة','is_stock'=>0,'is_fund'=>0,'parent_id'=>18,'is_basic'=>0,'employees_expensses'=>1],
            ['id'=>59,'code'=>'110101','deletable'=>0,'editable'=>1,'aname'=>'الصندوق الرئيسي','is_stock'=>0,'is_fund'=>1,'parent_id'=>23,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>60,'code'=>'110201','deletable'=>0,'editable'=>1,'aname'=>'البنك الرئيسي','is_stock'=>0,'is_fund'=>1,'parent_id'=>24,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>61,'code'=>'110301','deletable'=>0,'editable'=>1,'aname'=>'العميل النقدي','is_stock'=>0,'is_fund'=>0,'parent_id'=>25,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>62,'code'=>'110401','deletable'=>0,'editable'=>1,'aname'=>'المخزن الرئيسي','is_stock'=>1,'is_fund'=>0,'parent_id'=>26,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>63,'code'=>'110501','deletable'=>0,'editable'=>1,'aname'=>'حافظة أوراق القبض','is_stock'=>0,'is_fund'=>0,'parent_id'=>27,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>64,'code'=>'210101','deletable'=>0,'editable'=>1,'aname'=>'المورد النقدي','is_stock'=>0,'is_fund'=>0,'parent_id'=>41,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>65,'code'=>'210201','deletable'=>0,'editable'=>1,'aname'=>'الموظف الإفتراضى','is_stock'=>0,'is_fund'=>0,'parent_id'=>42,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>66,'code'=>'210301','deletable'=>0,'editable'=>1,'aname'=>'حافظة أوراق الدفع','is_stock'=>0,'is_fund'=>0,'parent_id'=>43,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>67,'code'=>'5301','deletable'=>0,'editable'=>0,'aname'=>'رواتب الموظفين','is_stock'=>0,'is_fund'=>0,'parent_id'=>22,'is_basic'=>0,'employees_expensses'=>1],
            // حسابات الضرايب
            ['id'=>68,'code'=>'210401','deletable'=>0,'editable'=>1,'aname'=>'الضرايب','is_stock'=>0,'is_fund'=>0,'parent_id'=>44,'is_basic'=>1 ,'employees_expensses'=>0],
            ['id'=>69,'code'=>'21040101','deletable'=>0,'editable'=>1,'aname'=>'ض ق م مبيعات','is_stock'=>0,'is_fund'=>0,'parent_id'=>68,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>70,'code'=>'21040102','deletable'=>0,'editable'=>1,'aname'=>'ض ق م مشتريات','is_stock'=>0,'is_fund'=>0,'parent_id'=>68,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>71,'code'=>'21040103','deletable'=>0,'editable'=>1,'aname'=>'ض خ مبيعات','is_stock'=>0,'is_fund'=>0,'parent_id'=>68,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>72,'code'=>'21040104','deletable'=>0,'editable'=>1,'aname'=>'ض خ مشتريات','is_stock'=>0,'is_fund'=>0,'parent_id'=>68,'is_basic'=>0,'employees_expensses'=>0],
            ['id'=>73,'code'=>'110801','deletable'=>0,'editable'=>1,'aname'=>'مركز التشغيل الرئيسي','is_stock'=>0,'is_fund'=>0,'parent_id'=>30,'is_basic'=>0,'employees_expensses'=>0],

            ['id'=>74,'code'=>'2105','deletable'=>0,'editable'=>0,'aname'=>'المصروفات المستحقه','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>75,'code'=>'1110','deletable'=>0,'editable'=>0,'aname'=>'إيرادات أخرى مستحقه','is_stock'=>0,'is_fund'=>0,'parent_id'=>6,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>76,'code'=>'2106','deletable'=>0,'editable'=>0,'aname'=>'إيرادات أخرى محصله مقدما','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>77,'code'=>'5302','deletable'=>0,'editable'=>0,'aname'=>'مصروف اهلاك الأصول','is_stock'=>0,'is_fund'=>0,'parent_id'=>22,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>78,'code'=>'13','deletable'=>0,'editable'=>0,'aname'=>'الأصول غير الملموسة','is_stock'=>0,'is_fund'=>0,'parent_id'=>1,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>79,'code'=>'22','deletable'=>0,'editable'=>0,'aname'=>'الخصوم الغير متداوله','is_stock'=>0,'is_fund'=>0,'parent_id'=>2,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>80,'code'=>'110601','deletable'=>0,'editable'=>0,'aname'=>'السلف','is_stock'=>0,'is_fund'=>0,'parent_id'=>28,'is_basic'=>1,'employees_expensses'=>0],
            
            ['id'=>81,'code'=>'2108','deletable'=>0,'editable'=>0,'aname'=>'أطراف ذات علاقه دائنه','is_stock'=>0,'is_fund'=>0,'parent_id'=>8,'is_basic'=>1,'employees_expensses'=>0],

            ['id'=>82,'code'=>'52','deletable'=>0,'editable'=>0,'aname'=>'مصروفات تشغيليه','is_stock'=>0,'is_fund'=>0,'parent_id'=>5,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>83,'code'=>'54','deletable'=>0,'editable'=>0,'aname'=>'مصروفات تمويليه','is_stock'=>0,'is_fund'=>0,'parent_id'=>5,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>84,'code'=>'541','deletable'=>0,'editable'=>0,'aname'=>'فوائد وأقساط القروض','is_stock'=>0,'is_fund'=>0,'parent_id'=>83,'is_basic'=>1,'employees_expensses'=>0],
            ['id'=>85,'code'=>'542','deletable'=>0,'editable'=>0,'aname'=>'مصاريف تمويليه أخري','is_stock'=>0,'is_fund'=>0,'parent_id'=>83,'is_basic'=>1,'employees_expensses'=>0],

            

        ];

        // ضف timestamps إن كانت الأعمدة موجودة
        $rows = array_map(fn ($r) => $r + ['crtime' => $now, 'mdtime' => $now], $rows);

        Schema::disableForeignKeyConstraints();
        // احذف السطر القادم لو لا تريد تفريغ الجدول
        DB::table('acc_head')->truncate();
        DB::table('acc_head')->insert($rows);
        Schema::enableForeignKeyConstraints();
    }
}
