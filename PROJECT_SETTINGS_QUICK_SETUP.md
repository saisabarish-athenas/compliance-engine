# PROJECT SETTINGS - QUICK SETUP GUIDE

## Step 1: Run Migrations

```bash
php artisan migrate
```

Expected output:
```
Migrating: 2026_02_24_141243_add_statutory_fields_to_tenants_table
Migrated:  2026_02_24_141243_add_statutory_fields_to_tenants_table

Migrating: 2026_02_24_141310_add_unit_fields_to_branches_table
Migrated:  2026_02_24_141310_add_unit_fields_to_branches_table
```

## Step 2: Access Settings Page

Navigate to: `http://localhost:8000/compliance/settings`

## Step 3: Fill Required Fields

### Establishment Details
- **Establishment Name** ✱ (e.g., "ABC Manufacturing Pvt Ltd")
- **Factory License Number** ✱ (e.g., "TN/FAC/2024/12345")
- **PF Code** (e.g., "TNCHE1234567000")
- **ESI Code** (e.g., "12-34-567890-000-0000")
- **Labour Office Address** (e.g., "Chennai Labour Office, Anna Salai")

### Branch/Unit Details (for each branch)
- **Unit Name** ✱ (e.g., "Chennai Unit 1")
- **Address** ✱ (e.g., "Plot No. 123, SIPCOT Industrial Park, Chennai - 600001")

✱ = Required field

## Step 4: Save Settings

Click "Save Settings" button.

Success message: "Statutory settings updated successfully"

## Step 5: Verify Configuration

```bash
php artisan compliance:system-check
```

Look for:
```
Checking Statutory Settings...
Statutory Settings: ✅ OK (All configured)
```

## Step 6: Test Form Generation

```bash
php artisan compliance:test-generation
```

Forms should now display:
- ✅ Establishment name (not N/A)
- ✅ Factory license number (not N/A)
- ✅ Branch/unit name (not N/A)
- ✅ Address (not N/A)

## Troubleshooting

### Error: "Statutory settings incomplete"

**Cause**: Missing establishment_name or factory_license_no

**Fix**: Go to `/compliance/settings` and fill required fields

### Error: "Branch details incomplete"

**Cause**: Missing unit_name or address for branch

**Fix**: Go to `/compliance/settings` and fill branch details

### Settings page not loading

**Cause**: Migration not run

**Fix**: Run `php artisan migrate`

### Changes not reflecting in forms

**Cause**: Cache issue

**Fix**: 
```bash
php artisan config:clear
php artisan cache:clear
```

## Sample Data

For testing, use:

```
Establishment Name: Tamil Nadu Textiles Manufacturing Ltd
Factory License No: TN/FAC/2024/00123
PF Code: TNCHE0012345000
ESI Code: 12-00-123456-000-0000
Labour Office Address: Regional Labour Commissioner Office, Chennai

Unit Name: Chennai Production Unit
Address: Plot No. 45, SIPCOT Industrial Estate, Irungattukottai, 
         Sriperumbudur Taluk, Kanchipuram District, Tamil Nadu - 602105
```

## Production Deployment

1. Run migrations on production database
2. Configure settings for each tenant
3. Run system check
4. Test sample form generation
5. Verify all 36 forms display correct details
6. Enable production mode

## Support

**Issue**: Settings not saving
**Check**: Database permissions, validation errors

**Issue**: Forms still showing N/A
**Check**: Settings saved correctly, cache cleared

**Issue**: System check failing
**Check**: All required fields filled for all tenants/branches
