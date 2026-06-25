@echo off
title GMS - Stop Dev
echo Stopping Tailwind watcher and PHP server...

REM Kill the named windows started by start-dev.bat
taskkill /FI "WINDOWTITLE eq GMS - Tailwind Watch*" /T /F >nul 2>nul
taskkill /FI "WINDOWTITLE eq GMS - PHP Server :8000*" /T /F >nul 2>nul

REM Fallback: kill any php process bound to the dev server
for /f "tokens=5" %%P in ('netstat -ano ^| findstr ":8000" ^| findstr "LISTENING"') do taskkill /PID %%P /F >nul 2>nul

echo Done.
timeout /t 2 /nobreak >nul
exit /b 0
