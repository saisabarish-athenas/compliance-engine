@echo off
setlocal enabledelayedexpansion

echo.
echo 🚀 Starting Complete Compliance Engine Setup...
echo.

echo 1️⃣  Clearing cache...
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo.
echo 2️⃣  Resetting database...
php artisan migrate:reset --force
php artisan migrate

echo.
echo 3️⃣  Seeding fresh demo data...
php artisan db:seed --class=FreshComplianceSeeder

echo.
echo 4️⃣  Verifying data...
php artisan tinker ^
  $tenants = DB::table('tenants')->count(); ^
  $branches = DB::table('branches')->count(); ^
  $employees = DB::table('workforce_employee')->count(); ^
  $payroll = DB::table('workforce_payroll_entry')->count(); ^
  echo "\n✅ Data Verification:\n"; ^
  echo "  Tenants: $tenants\n"; ^
  echo "  Branches: $branches\n"; ^
  echo "  Employees: $employees\n"; ^
  echo "  Payroll Entries: $payroll\n";

echo.
echo ✅ Setup Complete!
echo.
echo Next steps:
echo   1. Start the server: php artisan serve
echo   2. Generate forms: php artisan compliance:trace-form-data --tenant_id=1 --branch_id=1
echo   3. Preview forms: Visit http://localhost:8000/compliance/forms
echo.

pause
