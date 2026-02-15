@echo off
chcp 65001 > nul

REM الانتقال إلى مجلد السكريبت
cd /d "%~dp0"

echo ========================================
echo تجميع وكيل طباعة المطبخ
echo ========================================
echo.

REM التحقق من وجود ملف المصدر
if not exist "PrintAgent.cs" (
    echo ❌ ملف PrintAgent.cs غير موجود في المجلد الحالي
    echo.
    echo المجلد الحالي: %CD%
    echo.
    pause
    exit /b 1
)

echo ✅ تم العثور على ملف المصدر
echo.

REM البحث عن مجلد .NET Framework
set "DOTNET_PATH=C:\Windows\Microsoft.NET\Framework64\v4.0.30319"

if not exist "%DOTNET_PATH%\csc.exe" (
    set "DOTNET_PATH=C:\Windows\Microsoft.NET\Framework\v4.0.30319"
)

if not exist "%DOTNET_PATH%\csc.exe" (
    echo ❌ لم يتم العثور على .NET Framework
    echo.
    echo يرجى تثبيت .NET Framework 4.0 أو أحدث
    pause
    exit /b 1
)

echo ✅ تم العثور على .NET Framework
echo المسار: %DOTNET_PATH%
echo.

echo 🔨 جاري التجميع...
echo.
"%DOTNET_PATH%\csc.exe" /target:exe /out:PrintAgent.exe /reference:System.Web.Extensions.dll /reference:System.Drawing.dll PrintAgent.cs

if errorlevel 1 (
    echo.
    echo ❌ فشل التجميع
    echo.
    echo تحقق من الأخطاء أعلاه
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✅ تم التجميع بنجاح!
echo ========================================
echo.
echo تم إنشاء الملف: PrintAgent.exe
echo الموقع: %CD%\PrintAgent.exe
echo.
echo لتشغيل الوكيل:
echo   1. انقر نقراً مزدوجاً على PrintAgent.exe
echo   2. أو استخدم: start-admin.bat
echo.
echo ملاحظة: يفضل تشغيل البرنامج كمسؤول (Run as Administrator)
echo.
pause
