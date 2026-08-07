@echo off
cd /d "%~dp0"
echo Starting D Star Company Print Proxy...
echo.
echo The proxy runs on http://localhost:9999
echo Keep this window open while using the POS.
echo.
node server.js
pause
