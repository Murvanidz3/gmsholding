@echo off
setlocal EnableDelayedExpansion
title GMS - Build, Verify, Sync & Push
cd /d "%~dp0"

echo ============================================
echo   GMS - Commit and Push to GitHub
echo ============================================
echo.

REM Enable repo hooks so plain "git push" also syncs first
git config core.hooksPath .githooks 2>nul

if exist ".git\index.lock" del /f /q ".git\index.lock"

for /f "delims=" %%i in ('git config user.email 2^>nul') do set "GE=%%i"
if "!GE!"=="" (
  git config user.email "murvanidzel@gmail.com"
  git config user.name  "Murvanidz3"
)

echo [1/6] Building Tailwind CSS...
call npm run build
if errorlevel 1 ( echo ERROR: build failed. & pause & exit /b 1 )

echo.
echo [2/6] Verifying integrity...
call node verify.js
if errorlevel 1 ( echo. & echo ABORTED: integrity check failed. & pause & exit /b 1 )

echo.
echo [3/6] Staging changes...
git add -A

echo.
echo [4/6] Committing...
if exist ".git\MERGE_HEAD" (
  git diff --cached --quiet
  if errorlevel 1 (
    git commit --no-edit
  ) else (
    git commit --no-edit -m "Merge remote-tracking branch 'origin/main'"
  )
  if errorlevel 1 ( echo ERROR: merge commit failed. & pause & exit /b 1 )
  echo    Merge completed.
) else (
  git diff --cached --quiet
  if not errorlevel 1 (
    echo    Nothing new to commit.
  ) else (
    set "MSG=%~1"
    if "!MSG!"=="" set "MSG=Update site %date% %time%"
    git commit -m "!MSG!"
    if errorlevel 1 ( echo ERROR: commit failed. & pause & exit /b 1 )
    echo    Committed.
  )
)

echo.
echo [5/6] Syncing with remote (local wins on conflict)...
git fetch origin
if errorlevel 1 ( echo ERROR: fetch failed - check network. & pause & exit /b 1 )

for /f %%i in ('git rev-list --count HEAD..origin/main 2^>nul') do set "BEHIND=%%i"
if not "!BEHIND!"=="0" (
  echo    Pulling !BEHIND! remote commit^(s^)...
  git pull origin main --no-rebase -X ours --no-edit
  if errorlevel 1 (
    echo    Retry with unrelated histories...
    git pull origin main --no-rebase -X ours --no-edit --allow-unrelated-histories
    if errorlevel 1 ( echo ERROR: pull failed. & pause & exit /b 1 )
  )
) else (
  echo    Already up to date with remote.
)

echo.
echo [6/6] Pushing to origin/main...
git branch -M main
git push -u origin main
if errorlevel 1 ( echo. & echo ERROR: push failed - check GitHub login / network. & pause & exit /b 1 )

echo.
echo ----- STATUS -----
git log --oneline -3
echo ------------------
echo DONE. Actions: https://github.com/Murvanidz3/gmsholding/actions
echo.
pause
