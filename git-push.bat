@echo off
setlocal
title GMS - Build, Verify & Push
cd /d "%~dp0"

echo ============================================
echo   GMS - Commit and Push to GitHub
echo ============================================
echo.

REM Clear any stale git lock (left by a crashed git/GUI)
if exist ".git\index.lock" del /f /q ".git\index.lock"

REM 1) Production CSS build
echo [1/4] Building Tailwind CSS...
call npm run build
if errorlevel 1 ( echo ERROR: build failed. & pause & exit /b 1 )

REM 2) Integrity gate - never push corrupt data
echo.
echo [2/4] Verifying project integrity...
call node verify.js
if errorlevel 1 ( echo. & echo ABORTED: integrity check failed - fix before pushing. & pause & exit /b 1 )

REM 3) Commit
echo.
echo [3/4] Committing...
set "MSG=%~1"
if "%MSG%"=="" set "MSG=Update site %date% %time%"
git add -A
git commit -m "%MSG%"
if errorlevel 1 echo (Nothing new to commit - continuing to push.)

REM 4) Push
echo.
echo [4/4] Pushing to origin/main...
git branch -M main
git push -u origin main
if errorlevel 1 ( echo ERROR: push failed (check GitHub login / network). & pause & exit /b 1 )

echo.
echo ============================================
echo   DONE - push complete.
echo   GitHub Actions will now build ^& deploy to Hostinger.
echo   Watch: https://github.com/Murvanidz3/gmsholding/actions
echo ============================================
pause
