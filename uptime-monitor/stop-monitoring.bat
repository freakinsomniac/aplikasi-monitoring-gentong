@echo off
echo ============================================
echo   UPTIME MONITOR - STOP ALL SERVICES
echo ============================================
echo.

echo [%time%] Stopping Laravel Uptime Monitor Services...
echo.

REM Stop Laravel Server
echo [%time%] Stopping Laravel Server...
taskkill /fi "windowtitle eq Laravel Server*" /f >nul 2>&1
if %errorlevel% equ 0 (
    echo  ^> Laravel Server stopped successfully
) else (
    echo  ^> Laravel Server was not running
)

REM Stop Queue Worker  
echo [%time%] Stopping Queue Worker...
taskkill /fi "windowtitle eq Queue Worker*" /f >nul 2>&1
if %errorlevel% equ 0 (
    echo  ^> Queue Worker stopped successfully
) else (
    echo  ^> Queue Worker was not running
)

REM Stop Scheduler
echo [%time%] Stopping Monitor Checks...
taskkill /fi "windowtitle eq Monitor Checks*" /f /t >nul 2>&1
if %errorlevel% equ 0 (
    echo  ^> Monitor Checks stopped successfully
) else (
    echo  ^> Monitor Checks was not running
)

echo.
echo ============================================
echo    ALL SERVICES STOPPED SUCCESSFULLY!
echo ============================================
echo.

REM Optional: Kill any remaining php artisan processes
echo Cleaning up any remaining PHP processes...
taskkill /f /im php.exe >nul 2>&1

echo.
echo All monitoring services have been terminated.
echo You can restart them by running "start-monitoring.bat"
echo.
pause