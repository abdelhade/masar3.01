<?php 
include('../includes/connect.php');  

// استقبال البيانات من النموذج
$employeeid = $_POST['employee']; 
$startdate = $_POST['startdate']; 
$startnum = new DateTime($startdate); 
$enddate = $_POST['enddate']; 
$endnum = new DateTime($enddate);  

// التحقق من وجود سجلات مسبقة في نفس الفترة
$sqlchkdur = "SELECT * FROM attlog WHERE employee = $employeeid AND day >= '$startdate' AND day < '$enddate'";
$rowchkdur = $conn->query($sqlchkdur)->fetch_assoc();
if (isset($rowchkdur)) {
    echo "<h1> يوجد سجلات في الفتره المحدده من فضلك تأكد من الفتره<button style='font-size:40px'><a href='../add_calcsalary.php'>رجوع</a></button></h1> ";
    die;
}

// حساب عدد الأيام بين تاريخ البداية والنهاية
$interval = $startnum->diff($endnum);
$dayscount = $interval->days + 1; // يشمل يوم البداية

// استرجاع بيانات الموظف
$rowemp = $conn->query("SELECT * FROM employees WHERE id = $employeeid")->fetch_assoc();
$ent_tybe = $rowemp['ent_tybe'];
$hour_extra = $rowemp['hour_extra'];
$day_extra = $rowemp['day_extra'];

// استرجاع بيانات الشيفت الخاص بالموظف
$shift = $rowemp['shift'];
$rowshft = $conn->query("SELECT * FROM shifts WHERE id = $shift")->fetch_assoc();

$shiftstart = $rowshft['shiftstart'];
$shiftend = $rowshft['shiftend'];
$instart = $rowshft['instart'];
$inend = $rowshft['inend'];
$outstart = $rowshft['outstart'];
$outend = $rowshft['outend'];
$workingdays = $rowshft['workingdays'];
$wdarray = explode(",", $workingdays); // تحويل الأيام إلى مصفوفة

// حلقة لكل يوم في الفترة المحددة
for ($i = 0; $i < $dayscount; $i++) {
    $curday = $startnum->format('Y-m-d');
    $cdate = new DateTime($curday);
    $dayofweek = $cdate->format('N'); // رقم اليوم في الأسبوع (1=الاثنين)

    // حساب عدد ساعات العمل المحددة بالشيفت
    $time1 = strtotime($shiftend);
    $time2 = strtotime($shiftstart);
    $time_difference_in_seconds = $time1 - $time2;
    $time_difference_hours = floor($time_difference_in_seconds / 3600);
    $time_difference_minutes = floor(($time_difference_in_seconds % 3600) / 60);
    $time_difference_seconds = $time_difference_in_seconds % 60;

    // تحديد إذا ما كان اليوم يوم عمل أم لا
    $statue = in_array($dayofweek, $wdarray) ? 1 : 0;

    // استعلام البصمة الأولى (الدخول)
    $sqlfpin = "SELECT MIN(time) AS fpin FROM attandance WHERE employee = '$employeeid' AND fpdate = '$curday' AND time > '$instart' AND time < '$inend'";
    $rowfpin = $conn->query($sqlfpin)->fetch_assoc();
    $fpin = $rowfpin['fpin'];

    if (!$fpin == null) {
        $statue = 2; // تم الحضور
    }

    // معالجة الخروج حسب ما إذا كان الشيفت يمتد بعد منتصف الليل
    $shiftstart_time = new DateTime($shiftstart);
    $shiftend_time = new DateTime($shiftend);

    if ($shiftend_time > $shiftstart_time) {
        // شيفت عادي لا يعبر منتصف الليل
        $sqlfpout = "SELECT MAX(time) AS fpout FROM attandance WHERE employee = '$employeeid' AND fpdate = '$curday' AND time > '$outstart' AND time < '$outend'";
        $rowfpout = $conn->query($sqlfpout)->fetch_assoc();
        $fpout = $rowfpout['fpout'];
    } elseif ($shiftend_time < $shiftstart_time) {
        // الشيفت يعبر منتصف الليل
        $curday = (new DateTime($curday))->modify('+1 day')->format('Y-m-d');

        $sqlfpout = "SELECT MAX(time) AS fpout FROM attandance WHERE employee = '$employeeid' AND fpdate = '$curday' AND time > '$outstart' AND time < '$outend'";
        $rowfpout = $conn->query($sqlfpout)->fetch_assoc();
        $fpout = $rowfpout['fpout'];

        $fpout_time = new DateTime($fpout);
        $fpout_time->modify('+24 hours');
        $fpout = $fpout_time->format('H:i:s');

        $hours = $fpout_time->format('H');
        $minutes_seconds = $fpout_time->format(':i:s');
        $fpout = ($hours + 24) . $minutes_seconds;

        $curday = $startnum->format('Y-m-d'); // إعادة اليوم الأصلي
    }

    if (!$fpout == null) {
        $statue = 2;
    }

    // معالجة الوقت إذا تجاوز 24 ساعة
    list($hours, $minutes, $seconds) = array_pad(explode(':', $fpout), 3, '00');
    if ($hours >= 24) {
        $hours -= 24;
        $fpout = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        $time3 = strtotime($fpout) + 86400;
    } else {
        $time3 = strtotime($fpout);
    }

    $time4 = strtotime($fpin);
    $time_difference2 = $time3 - $time4;

    // حساب عدد ساعات العمل الفعلية
    if (!$fpout == null && !$fpin == null) {
        $time_difference_hours2 = round(($time_difference2 / 3600), 2);
    } elseif ($fpout == null && $fpin == null) {
        $time_difference_hours2 = 0;
    } else {
        $time_difference_hours2 = ($time_difference_hours / 2); // تقدير متوسط
    }

    // حساب أجر الساعة الواحدة
    $dfh = ($rowemp['salary'] / 30) / $time_difference_hours;
    $dueforhour = round($dfh, 2);
    $realdue = floor($dueforhour * $time_difference_hours2);

    // حفظ السجل في جدول attlog
    $sqllog = ("INSERT INTO attlog 
    (employee, day, starttime, endtime, fpin, fpout, defhours, curhours, dueforhour, realdue, statue) 
    VALUES 
    ('$employeeid','$curday','$shiftstart','$shiftend','$fpin','$fpout','$time_difference_hours ','$time_difference_hours2','$dueforhour','$realdue','$statue')");

    $conn->query($sqllog);

    // الانتقال لليوم التالي
    $startnum->add(new DateInterval('P1D'));
}

// حساب أيام الغياب والعطلات
$sqlgetatt = "SELECT COUNT(*) AS holidays FROM attlog WHERE statue = '0' AND employee = '$employeeid' AND day >= '$startdate' AND day <= '$enddate'";
$reshol = $conn->query($sqlgetatt);
$rowhol = $reshol->fetch_assoc();
$holidays = $rowhol['holidays'];

$workdays = $dayscount - $holidays;
$exphours = $time_difference_hours * $workdays;

// ساعات العمل الفعلية
$sqlacchours = "SELECT SUM(curhours) AS curhours FROM attlog WHERE statue = '2' AND employee = '$employeeid' AND day >= '$startdate' AND day <= '$enddate'";
$rowacchours = $conn->query($sqlacchours)->fetch_assoc();
$accualhours = round($rowacchours['curhours'], 2);

// عدد أيام الحضور
$sqlcountatt = "SELECT COUNT(*) AS attdays FROM attlog WHERE statue = '2' AND employee = '$employeeid' AND day >= '$startdate' AND day <= '$enddate'";
$rowcountatt = $conn->query($sqlcountatt)->fetch_assoc();
$attdays = $rowcountatt['attdays'];

// عدد أيام الغياب
$rowcountabs = $conn->query("SELECT COUNT(*) AS absdays FROM attlog WHERE statue = '2' AND employee = '$employeeid' AND day > '$startdate' AND day < '$enddate'")->fetch_assoc();
$absdays = $rowcountabs['absdays'];

// ملخص معلومات الفترة
$info = " احتساب الرواتب من يوم " . $startdate . " الي يوم " . $enddate;

// حساب أجر الساعة
$titleperhour = round($rowemp['salary'] / $exphours , 2);

// حساب الساعات الإضافية
$extrasql = "SELECT SUM(curhours - defhours) AS total_hours FROM attlog WHERE curhours > defhours AND statue != 0 AND employee = '$employeeid' AND day >= '$startdate' AND day <= '$enddate'";
$extra_time_hours = $conn->query($extrasql)->fetch_assoc();

$extra_time_period = $conn->query("SELECT SUM(curhours) - SUM(defhours) AS total_difference FROM attlog where statue != 0 AND employee = '$employeeid' AND day >= '$startdate' AND day <= '$enddate'")->fetch_assoc();

$ext_hours = $extra_time_hours['total_hours'];
$ext_period = $extra_time_period['total_difference'];
$basic_hours = $accualhours - $ext_hours;
$basic_period = $accualhours - $ext_period;

$ext_hours_ent = $ext_hours * $titleperhour *  $hour_extra;
$ext_hours_basic = $ext_hours * $titleperhour;
$basic_hours_ent = ($basic_hours * $titleperhour );

// حساب المستحقات حسب نوع الاستحقاق
if ($ent_tybe == 1) {
    $info = " احتساب الرواتب من يوم " . $startdate . " الي يوم " . $enddate . " بنظام الاستحقاق بالساعات فقط";
    $entitle =  round($titleperhour * $accualhours ,2 );
} elseif ($ent_tybe == 2) {
    $info = " احتساب الرواتب من يوم " . $startdate . " الي يوم " . $enddate . " بنظام الاستحقاق ساعات عمل و اضافي يومي";
    $entitle = round($titleperhour * $accualhours ,2 ) + $ext_hours_ent - $ext_hours_basic ;
} elseif ($ent_tybe == 3) {
    $info = " احتساب الرواتب من يوم " . $startdate . " الي يوم " . $enddate . " بنظام الاستحقاق ساعات عمل و اضافي خلال الفترة";
    if ($ext_period < 0) {
        $entitle = $accualhours * $titleperhour;
    } elseif ($ext_period > 0) {
        $entitle = (($accualhours - $ext_period) * $titleperhour) + ($ext_period * $titleperhour *  $hour_extra);
    }
} elseif ($ent_tybe == 4) {
    $info = " احتساب الرواتب من يوم " . $startdate . " الي يوم " . $enddate . " بنظام الاستحقاق بناء علي الحضور";
    $entitle = round($attdays * ($rowemp['salary'] / $workdays ), 2);
} elseif ($ent_tybe == 5) {
    $info = " احتساب الرواتب من يوم " . $startdate . " الي يوم " . $enddate . " بنظام الاستحقاق بنظام الحضور فقط";
    $entitle = 0;
}

// حفظ ملخص الحضور في جدول attdocs
$sqlattdocs = "INSERT INTO attdocs 
(empid, fromdate, todate, alldays, workdays, exphours, accualhours, attdays, absdays, holidays, earlyminits, info, entitle)
VALUES
('$employeeid','$startdate','$enddate','$dayscount','$workdays','$exphours','$accualhours','$attdays','$absdays','$holidays','0','$info' , '$entitle')";
$conn->query($sqlattdocs);
$docid = $conn->insert_id;

// ربط كل سجلات attlog بالملخص الجديد
$sqlupdate = "UPDATE attlog SET attdoc = '$docid' WHERE day >= '$startdate'  AND day <= '$enddate' And employee = $employeeid";
$conn->query($sqlupdate);

// تسجيل العملية
$conn->query("INSERT INTO process(type) VALUES ('add calcsalary')");

// إعادة التوجيه
header('location:../calcsalary.php');

include('../includes/footer.php');
==================================================


Project Objectives
Primary Goals:
Security Enhancement: Eliminate SQL injection vulnerabilities and implement proper authentication
Code Modernization: Convert legacy PHP to Laravel 12 with Livewire 3
Maintainability: Implement clean architecture with service layers
Scalability: Design for future growth and feature additions
User Experience: Improve UI/UX with modern Arabic interface
Success Metrics:
100% elimination of security vulnerabilities
90% reduction in code complexity
80% improvement in processing speed
95% test coverage
Zero data loss during migration
🏗️ System Architecture
Technology Stack:
Backend: Laravel 12, PHP 8.2+
Frontend: Livewire 3, Alpine.js, Bootstrap 5
Database: MySQL 8.0+
Authentication: Laravel Sanctum
Testing: PHPUnit, Pest
Localization: Laravel Localization (Arabic)
Architecture Pattern: