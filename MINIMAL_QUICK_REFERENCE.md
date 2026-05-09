# MINIMAL Subscription - Quick Reference

## 🚀 Quick Start

### For MINIMAL Users (minimal@demo.com)

1. **Login** → Dashboard
2. **Create Batch** → Select section, forms, month/year
3. **Click "Enter Statutory Data"** → Opens data entry form
4. **Fill in required fields** → Auto-saves
5. **Preview forms** → Verify data appears correctly
6. **Generate Forms** → Click "Generate Forms" button
7. **Download Report** → Get final PDF package

---

## 📋 Data Entry Fields

### Required for Most Forms
- Establishment Name
- Address
- Total Employees
- Gross Wages
- Working Days

### Optional (form-specific)
- Contractor details (CLRA forms)
- Accident details (Form 18, Form 26)
- PF/ESI codes (payroll forms)

---

## 🔄 Subscription Comparison

| Feature | MINIMAL | FULL |
|---------|---------|------|
| Data Entry | Manual web form | Automated from database |
| Preview | ✅ Yes | ✅ Yes |
| Form Generation | ✅ Auto from manual data | ✅ Auto from database |
| Inspection Pack | ❌ No | ✅ Yes |
| Digital Signature | ❌ No | ✅ Yes |
| Database Required | ❌ No | ✅ Yes |

---

## 🛠️ Technical Details

### Routes
```php
// Manual data entry
GET  /compliance/manual-data/{month}/{year}
POST /compliance/manual-data/{month}/{year}

// Preview (both subscriptions)
GET  /compliance/batch/{batch}/preview/{form}

// Generate (both subscriptions)
POST /compliance/batch/process/{id}
```

### Data Storage
```sql
-- MINIMAL: Manual data
SELECT * FROM statutory_manual_data 
WHERE tenant_id = ? AND month = ? AND year = ?;

-- FULL: Database tables
SELECT * FROM workforce_payroll_entry ...
```

### Code Detection
```php
// Check subscription
$subscription = auth()->user()->tenant->subscription_type;

if ($subscription === 'MINIMAL') {
    // Use ManualDataAdapter
} else {
    // Use FormDataAggregator
}
```

---

## 🐛 Troubleshooting

### Issue: Data not appearing in preview
**Solution:** Ensure data is saved (check for success message)

### Issue: Form generation fails
**Solution:** Check required fields are filled

### Issue: Preview shows empty form
**Solution:** Verify month/year matches batch period

### Issue: Cannot access data entry
**Solution:** Verify subscription is MINIMAL

---

## 📁 Key Files

```
Models:
- app/Models/StatutoryManualData.php

Services:
- app/Services/Compliance/ManualStatutoryDataRepository.php
- app/Services/Compliance/ManualDataAdapter.php

Controllers:
- app/Http/Controllers/ManualDataController.php

Views:
- resources/views/compliance/manual_data_entry.blade.php

Generators:
- app/Services/Compliance/FormGenerator/BaseFormGenerator.php (modified)
```

---

## ✅ Verification Commands

```bash
# Check migration
php artisan migrate:status | grep statutory_manual_data

# Clear cache
php artisan config:clear && php artisan route:clear

# Check routes
php artisan route:list | grep manual-data

# Test data entry
curl -X POST http://localhost/compliance/manual-data/1/2024 \
  -H "Content-Type: application/json" \
  -d '{"establishment": {"name": "Test"}}'
```

---

## 🎯 Success Criteria

✅ MINIMAL users can enter data via web form
✅ Data persists in database
✅ Preview shows entered data
✅ Forms generate successfully
✅ PDF format matches FULL subscription
✅ FULL subscription unchanged
✅ No errors in logs

---

## 📞 Quick Help

**Login Issues:** Check user tenant assignment
**Data Not Saving:** Check CSRF token, network tab
**Preview Empty:** Verify data saved for correct month/year
**Generation Fails:** Check logs at `storage/logs/laravel.log`

---

## 🔐 Security Notes

- Manual data isolated by tenant_id
- CSRF protection on all forms
- Subscription checks on all routes
- No cross-tenant data access
- Audit logs maintained

---

## 📊 Database Schema

```sql
CREATE TABLE statutory_manual_data (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    month INT NOT NULL,
    year INT NOT NULL,
    establishment_details JSON,
    employer_details JSON,
    employee_summary JSON,
    wage_summary JSON,
    attendance_summary JSON,
    accident_details JSON,
    contractor_summary JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(tenant_id, month, year)
);
```

---

## 🚦 Status Indicators

**Dashboard:**
- "Awaiting Data Entry" → Need to enter data
- "Processing" → Forms being generated
- "Completed" → Ready to download

**Data Entry:**
- Green checkmark → Data saved
- No indicator → Not saved yet

---

## 💡 Pro Tips

1. **Save frequently** - Form auto-saves on submit
2. **Preview before generating** - Verify data accuracy
3. **Use consistent data** - Same format across months
4. **Keep records** - Download reports for reference
5. **Test with sample data** - Verify before production use
