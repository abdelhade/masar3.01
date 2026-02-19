@echo off
chcp 65001 > nul

REM التحقق من وجود الملف
if not exist PrintAgent.exe (
    echo ❌ الملف PrintAgent.exe غير موجود
    echo.
    echo يرجى تشغيل compile.bat أولاً لتجميع البرنامج
    echo.
    pause
    exit /b 1
)

REM تشغيل البرنامج بصلاحيات المسؤول
echo ========================================
echo تشغيل وكيل طباعة المطبخ (كمسؤول)
echo ========================================
echo.

powershell -Command "Start-Process PrintAgent.exe -Verb RunAs"

echo.
echo تم تشغيل البرنامج في نافذة منفصلة
echo.
pause
