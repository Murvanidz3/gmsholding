@echo off
setlocal EnableDelayedExpansion
title GMS - Build, Verify & Push
cd /d "%~dp0"

echo ============================================
echo   GMS - Commit and Push to GitHub
echo ============================================
echo.

REM --- Clear any stale git lock ---
if exist ".git\index.lock" del /f /q ".git\index.lock"

REM --- Ensure a git identity exists (commits fail silently without it) ---
for /f "delims=" %%i in ('git config user.email 2^>nul') do set "GE=%%i"
if "!GE!"=="" (
  echo Setting a local git identity...
  git config user.email "murvanidzel@gmail.com"
  git config user.name  "Murvanidz3"
)

REM --- 1) Build CSS ---
echo [1/5] Building Tailwind CSS...
call npm run build
if errorlevel 1 ( echo ERROR: build failed. & pause & exit /b 1 )

REM --- 2) Integrity check ---
echo.
echo [2/5] Verifying integrity...
call node verify.js
if errorlevel 1 ( echo. & echo ABORTED: integrity check failed. & pause & exit /b 1 )

REM --- 3) Stage ---
echo.
echo [3/5] Staging changes...
git add -A

REM --- 4) Commit (only if there is something staged) ---
echo.
echo [4/5] Committing...
git diff --cached --quiet
if not errorlevel 1 (
  echo    Nothing new to commit. Working tree matches last commit.
) else (
  set "MSG=%~1"
  if "!MSG!"=="" set "MSG=Update site %date% %time%"
  git commit -m "!MSG!"
  if errorlevel 1 ( echo ERROR: commit failed - see message above. & pause & exit /b 1 )
  echo    Committed.
)

REM --- 5) Push ---
echo.
echo [5/5] Pushing to origin/main...
git branch -M main
git push -u origin main
if errorlevel 1 ( echo. & echo ERROR: push failed - check GitHub login / network. & pause & exit /b 1 )

echo.
echo ----- STATUS -----
git remote -v
echo.
git log --oneline -3
echo ------------------
echo.
echo DONE. If GitHub still shows old files, you are likely viewing a
echo different branch - the push went to 'main'.
echo Actions: https://github.com/Murvanidz3/gmsholding/actions
echo.
pause
