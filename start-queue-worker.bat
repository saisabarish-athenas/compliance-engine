@echo off
cd /d e:\compliance-engine
echo Starting Queue Worker...
php artisan queue:work --queue=compliance --tries=1
pause
