@echo off
chcp 65001 > nul
echo ========================================
echo تجميع وكيل طباعة المطبخ
echo ========================================
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
"%DOTNET_PATH%\csc.exe" /target:exe /out:PrintAgent.exe /reference:System.Web.Extensions.dll /reference:System.Drawing.dll PrintAgent.cs

if errorlevel 1 (
    echo.
    echo ❌ فشل التجميع
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✅ تم التجميع بنجاح!
echo ========================================
echo.
echo تم إنشاء الملف: PrintAgent.exe
echo.
echo لتشغيل الوكيل:
echo   1. انقر نقراً مزدوجاً على PrintAgent.exe
echo   2. أو استخدم: start.bat
echo.
echo ملاحظة: قد تحتاج لتشغيل البرنامج كمسؤول (Run as Administrator)
echo.
pause
