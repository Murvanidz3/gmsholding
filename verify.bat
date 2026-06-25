@echo off
title GMS - Verify Project Integrity
cd /d "%~dp0"
echo Running project integrity checks...
echo.
node verify.js
echo.
echo (NUL bytes = M: drive truncation. JS/JSON errors = broken build.)
pause
