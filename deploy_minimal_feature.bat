@echo off
REM ============================================
REM MINIMAL Subscription Deployment Script
REM ============================================

echo.
echo ========================================
echo  MINIMAL Subscription Deployment
echo ========================================
echo.

echo [1/5] Running database migration...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)
echo ✓ Migration completed

echo.
echo [2/5] Clearing configuration cache...
php artisan config:clear
echo ✓ Config cache cleared

echo.
echo [3/5] Clearing route cache...
php artisan route:clear
echo ✓ Route cache cleared

echo.
echo [4/5] Clearing view cache...
php artisan view:clear
echo ✓ View cache cleared

echo.
echo [5/5] Verifying routes...
php artisan route:list | findstr "manual-data"
echo ✓ Routes verified

echo.
echo ========================================
echo  Deployment Complete!
echo ========================================
echo.
echo Next Steps:
echo 1. Login as minimal@demo.com
echo 2. Create a compliance batch
echo 3. Click "Enter Statutory Data"
echo 4. Fill in the form and save
echo 5. Preview forms
echo 6. Generate forms
echo.
echo For FULL subscription verification:
echo 1. Login as full@demo.com
echo 2. Verify existing flow works
echo 3. No changes to behavior
echo.
echo Documentation:
echo - MINIMAL_MANUAL_DATA_IMPLEMENTATION.md
echo - MINIMAL_QUICK_REFERENCE.md
echo - MINIMAL_IMPLEMENTATION_CHANGE_SUMMARY.md
echo.
pause
