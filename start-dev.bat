@echo off
setlocal EnableDelayedExpansion
title GMS - Dev Launcher
cd /d "%~dp0"

echo ============================================
echo   GMS Construction - Local Dev Launcher
echo ============================================
echo.

REM --- 1. Ensure dependencies ---
if not exist "node_modules\" (
  echo [1/3] Installing npm dependencies...
  call npm install
  if errorlevel 1 ( echo ERROR: npm install failed. & pause & exit /b 1 )
) else (
  echo [1/3] Dependencies already installed. Skipping.
)
echo.

REM --- 2. Locate PHP (no XAMPP config needed) ---
set "PHP_BIN="
where php >nul 2>nul && set "PHP_BIN=php"
if not defined PHP_BIN if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if not defined PHP_BIN if exist "C:\laragon\bin\php\php.exe" set "PHP_BIN=C:\laragon\bin\php\php.exe"
if not defined PHP_BIN (
  for /d %%D in ("C:\laragon\bin\php\php-*") do if exist "%%D\php.exe" set "PHP_BIN=%%D\php.exe"
)
if not defined PHP_BIN (
  echo ERROR: PHP not found on PATH or in C:\xampp\php or C:\laragon\bin\php.
  echo Install PHP or add php.exe to PATH, then re-run.
  pause & exit /b 1
)
echo [2/3] Using PHP: !PHP_BIN!
echo.

REM --- 3. Start Tailwind watcher (own window) ---
echo [3/3] Starting Tailwind watcher + PHP server...
start "GMS - Tailwind Watch" cmd /k "npm run dev"

REM --- Start PHP built-in server (own window) ---
start "GMS - PHP Server :8000" cmd /k ""!PHP_BIN!" -S localhost:8000 -t "%~dp0.""

REM --- Give the server a moment, then open browser ---
timeout /t 2 /nobreak >nul
start "" "http://localhost:8000"

echo.
echo ============================================
echo   RUNNING
echo   Site : http://localhost:8000
echo   Two windows opened: Tailwind watch + PHP server.
echo   Close those windows (or run stop-dev.bat) to stop.
echo ============================================
echo You can close THIS window.
timeout /t 4 /nobreak >nul
exit /b 0
