@echo off
cd /d "%~dp0"

where php >nul 2>nul
if errorlevel 1 (
    echo PHP belum terpasang atau belum ada di PATH.
    echo Install PHP 8.3+ terlebih dahulu, misalnya lewat Laragon atau XAMPP.
    pause
    exit /b 1
)

if not exist "public\storage" php artisan storage:link

echo.
echo ================================================
echo   IT Selling berjalan
echo   Web admin : http://localhost:8000
echo   Login     : admin@itselling.test / password
echo   Tekan Ctrl+C untuk berhenti
echo ================================================
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause
