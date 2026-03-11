# Tamil Nadu Statutory Compliance Certification Engine

## Overview

This Compliance Certification Engine ensures **100% Tamil Nadu Government statutory compliance** by validating every form against legal requirements before allowing inspection pack downloads.

## Architecture

### Core Components

```
app/Services/Compliance/Validation/
├── StructuralFormatValidator.php      # Part 1: Format validation
├── LegalRuleValidator.php             # Part 2: Legal compliance
├── CrossFormValidator.php             # Part 3: Cross-form reconciliation
├── ComputationValidator.php           # Part 4: Calculation validation
├── LayoutIntegrityValidator.php       # Part 5: Layout preservation
└── ComplianceCertificationService.php # Part 6: Main orchestrator
```

## Validation Layers

### 1. Structural Format Validation
Verifies:
- ✅ Form title matches official TN rule name
- ✅ Section numbering correct
- ✅ Column sequence matches statutory order
- ✅ Mandatory header fields present
- ✅ Required footer declarations present
- ✅ Date format strictly dd-mm-yyyy
- ✅ Register numbering format correct
- ✅ Establishment details format matches TN norms

### 2. Legal Rule Validation
Validates:
- ✅ Required statutory fields
- ✅ Minimum wage compliance (₹450)
- ✅ Overtime calculation correctness (2x basic)
- ✅ ESI contribution calculation (0.75%)
- ✅ EPF validation (12%)
- ✅ Child labour prohibition checks (age >= 14)
- ✅ Gender-based register compliance
- ✅ Statutory threshold applicability
- ✅ NIL handling compliance

### 3. Cross-Form Reconciliation
Ensures consistency across:
- ✅ Employee count (Muster Roll vs Wage Register)
- ✅ Total wages reconciliation
- ✅ Overtime hours matching
- ✅ ESI employee list consistency
- ✅ Contractor vs principal employer matching

### 4. Computation Validation
Verifies:
- ✅ Wage = Basic + DA + Allowances
- ✅ Net Pay = Gross - Deductions
- ✅ Overtime rate >= 2x basic
- ✅ ESI contribution % correct
- ✅ EPF % correct
- ✅ Bonus calculation correct (8.33% minimum)

### 5. Layout Integrity Validation
Ensures:
- ✅ Column alignment preserved
- ✅ No missing headers
- ✅ No extra columns
- ✅ TN statutory sequence preserved
- ✅ Register format matches TN government notification

## Certification Workflow

```
1. Generate Forms
   ↓
2. Run Structural Validation
   ↓
3. Run Legal Rule Validation
   ↓
4. Run Computation Validation
   ↓
5. Run Layout Validation
   ↓
6. Run Cross-Form Validation
   ↓
7. Calculate Certification Score
   ↓
8. Score = 100? → CERTIFIED ✅
   Score < 100? → NOT CERTIFIED ❌
   ↓
9. Block Inspection Pack if NOT CERTIFIED
```

## Scoring System

### Violation Severity
- **Critical**: -50 points (e.g., child labour)
- **Major**: -10 points (structural, legal, computation)
- **Minor**: -2 points (layout, warnings)

### Certification Threshold
- **100%**: TN Statutory Inspection Ready ✅
- **< 100%**: NOT CERTIFIED - Resolve violations ❌

## Configuration

### TN Statutory Rules (`config/tn_statutory_rules.php`)

Each form includes:
```php
'FORM_B' => [
    'official_title' => 'Register of Wages (Factories)',
    'mandatory_headers' => ['establishment_name', 'period_month', 'period_year'],
    'required_row_fields' => ['employee_name', 'employee_id', 'wages'],
    'column_sequence' => ['sl_no', 'employee_name', 'employee_id', ...],
    'date_fields' => ['payment_date'],
    'min_wage' => 450,
    'esi_rate' => 0.75,
    'epf_rate' => 12,
    'check_child_labour' => true,
    'establishment_fields' => ['establishment_name', 'license_number'],
]
```

## API Endpoints

### Certify Batch
```http
POST /compliance/batch/{batchId}/certify
```

**Response:**
```json
{
  "status": "success",
  "certified": true,
  "score": 100,
  "certification_status": "TN Statutory Inspection Ready",
  "violations": [],
  "warnings": [],
  "critical_errors": [],
  "form_scores": {
    "FORM_B": 100,
    "FORM_25": 100
  }
}
```

### Get Certification Status
```http
GET /compliance/batch/{batchId}/certification-status
```

### Download Inspection Pack (Blocked if not certified)
```http
GET /compliance/batch/{batchId}/inspection-pack
```

## Database Schema

### compliance_certification_logs
```sql
CREATE TABLE compliance_certification_logs (
    id BIGINT PRIMARY KEY,
    batch_id BIGINT NOT NULL,
    form_code VARCHAR(50) NOT NULL,
    certification_score INT DEFAULT 0,
    certified BOOLEAN DEFAULT FALSE,
    violations JSON,
    certified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(batch_id, form_code)
);
```

## Usage

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Certify a Batch
```php
$certificationService = app(\App\Services\Compliance\Validation\ComplianceCertificationService::class);
$result = $certificationService->certifyBatch($batchId);

if ($result['certified']) {
    // Allow inspection pack download
} else {
    // Block and show violations
}
```

### 3. Check Certification Before Download
The system automatically checks certification when downloading inspection pack:
```php
// In ComplianceExecutionController::downloadInspectionPack()
$certificationResult = $certificationService->certifyBatch($batch);

if (!$certificationResult['certified'] || $certificationResult['score'] < 100) {
    return redirect()->back()->with('error', 'Batch not legally certified');
}
```

## Violation Examples

### Critical Violation
```json
{
  "type": "legal",
  "field": "rows[0].age",
  "message": "Child labour prohibited: Age below 14 years",
  "severity": "critical"
}
```

### Major Violation
```json
{
  "type": "computation",
  "field": "rows[0].gross_wages",
  "message": "Gross wages incorrect. Expected: ₹15000, Found: ₹14500"
}
```

### Cross-Form Violation
```json
{
  "type": "cross_form",
  "field": "employee_count",
  "message": "Employee count mismatch: FORM_25: 50, FORM_B: 48"
}
```

## Legal Compliance Guarantees

✅ **100% TN statutory format compliance**  
✅ **100% legal field validation**  
✅ **100% cross-form reconciliation**  
✅ **100% computation validation**  
✅ **Certification score = 100 only if perfect**  
✅ **Inspection pack allowed only when certified**

## Backward Compatibility

- ✅ No changes to existing generators
- ✅ No database schema changes (except new certification log table)
- ✅ Maintains existing audit system
- ✅ Works with both MINIMAL and FULL subscriptions

## Testing

### Test Certification
```php
// Test with valid data
$result = $certificationService->certifyBatch(1);
assert($result['certified'] === true);
assert($result['score'] === 100);

// Test with violations
$result = $certificationService->certifyBatch(2);
assert($result['certified'] === false);
assert(count($result['violations']) > 0);
```

## Support

For issues or questions:
1. Check violation messages in certification response
2. Review TN statutory rules configuration
3. Verify form data matches expected format
4. Check logs in `compliance_certification_logs` table

## Version

**Version**: 1.0.0  
**Date**: January 2024  
**Compliance Standard**: Tamil Nadu Government Statutory Requirements
