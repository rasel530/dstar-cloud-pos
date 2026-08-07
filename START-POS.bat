@echo off
cd /d "E:\POS Software\D Star Company"
echo Starting D Star Company POS on http://127.0.0.1:8000
php artisan serve --port=8000
pause
