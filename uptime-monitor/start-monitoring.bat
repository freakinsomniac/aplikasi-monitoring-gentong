@echo off
echo ============================================
echo    UPTIME MONITOR - AUTO START SERVICES
echo ============================================
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo ERROR: artisan file not found!
    echo Make sure you're running this from the Laravel project directory.
    pause
    exit /b 1
)

echo [%time%] Starting Laravel Uptime Monitor Services...
echo.

REM Start Laravel Server in background
echo [%time%] 1/3 Starting Laravel Server (http://localhost:8000)...
start "Laravel Server" /min cmd /c "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3 /nobreak >nul

REM Start Queue Worker in background
echo [%time%] 2/3 Starting Queue Worker...
start "Queue Worker" /min cmd /c "php artisan queue:work --timeout=60 --sleep=3 --tries=3"
timeout /t 2 /nobreak >nul

REM Start Scheduler Worker in background - using continuous loop mode for 10-second intervals
echo [%time%] 3/3 Starting Monitor Checks (10-second intervals)...
start "Monitor Checks" /min cmd /c "php artisan monitor:check --loop"
timeout /t 2 /nobreak >nul

echo.
echo ============================================
echo   ALL SERVICES STARTED SUCCESSFULLY! 
echo ============================================
echo.
echo Services running:
echo  ^> Laravel Server: http://localhost:8000
echo  ^> Queue Worker: Processing monitoring jobs
echo  ^> Monitor Checks: Checking monitors every 10 seconds
echo.
echo Check running services: tasklist /fi "windowtitle eq Laravel*"
echo.
echo To stop all services: run "stop-monitoring.bat"
echo.
echo Press any key to keep this window open...
pause