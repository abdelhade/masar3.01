@echo off
chcp 65001 > nul

if not exist PrintAgent.exe (
    echo ❌ الملف PrintAgent.exe غير موجود
    echo.
    echo يرجى تشغيل compile.bat أولاً لتجميع البرنامج
    echo.
    pause
    exit /b 1
)

echo ========================================
echo تشغيل وكيل طباعة المطبخ
echo ========================================
echo.
echo ملاحظة: إذا ظهرت رسالة خطأ متعلقة بالصلاحيات،
echo قم بإغلاق هذه النافذة وتشغيل start-admin.bat
echo.

PrintAgent.exe

pause
