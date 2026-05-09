# DIGITAL SIGNATURE MODULE - Legal Compliance System

## Overview
Enterprise-grade digital signature module with tamper detection, audit logging, and legal compliance for Tamil Nadu statutory forms.

## Architecture

### Database Schema

#### compliance_signatures
Stores signature metadata and document hashes for tamper detection.

Fields:
- `tenant_id` - Tenant isolation
- `branch_id` - Branch identification
- `form_code` - Form identifier (FORM_10, FORM_B, etc.)
- `batch_id` - Batch reference
- `signed_by_user_id` - User who signed
- `signatory_name` - Legal signatory name
- `signatory_designation` - Manager/Occupier designation
- `signature_type` - DRAWN/IMAGE/DIGITAL_CERT
- `signature_path` - Secure storage path
- `signature_hash` - SHA256 of signature data
- `document_hash` - SHA256 of PDF document
- `ip_address` - Signing IP for audit
- `signed_at` - Timestamp

Constraints:
- Unique: (batch_id, form_code) - One signature per form per batch
- Index: (tenant_id, batch_id) - Fast tenant queries
- Index: document_hash - Integrity verification

#### compliance_audit_logs
Complete audit trail of all compliance actions.

Fields:
- `tenant_id` - Tenant isolation
- `user_id` - User performing action
- `action` - FORM_SIGNED, FORM_GENERATED, BATCH_LOCKED, etc.
- `form_code` - Form identifier
- `batch_id` - Batch reference
- `ip_address` - Request IP
- `user_agent` - Browser/client info
- `metadata` - JSON additional data

#### compliance_execution_batches (Enhanced)
Added locking mechanism:
- `is_locked` - Prevents modification after signing
- `locked_at` - Lock timestamp
- `locked_by_user_id` - User who locked

## Security Features

### 1. Tamper Detection

**Hash Generation**:
```php
$documentHash = hash_file('sha256', $pdfPath);
```

**Verification**:
```php
$currentHash = hash_file('sha256', $pdfPath);
if ($currentHash !== $storedHash) {
    throw new IntegrityViolationException();
}
```

### 2. Tenant Isolation

All operations validate:
- Batch belongs to tenant
- Branch belongs to tenant
- User belongs to tenant

```php
ComplianceContextValidator::validate($tenantId, $branchId, $month, $year);
```

### 3. Form Locking

Once signed:
- Form cannot be regenerated
- Batch cannot be reprocessed
- Dataset cannot be modified
- Only admin can unlock

### 4. Audit Trail

Every action logged:
- Form generation
- Form signing
- Form download
- Batch locking/unlocking
- Integrity violations

## API Endpoints

### Sign Form
```http
POST /compliance/sign/{batch}/{form}
Content-Type: application/json

{
  "signatory_name": "John Doe",
  "signatory_designation": "Factory Manager",
  "signature_type": "DRAWN",
  "signature_data": "data:image/png;base64,..."
}
```

Response:
```json
{
  "success": true,
  "message": "Form signed successfully",
  "data": {
    "signature_id": 123,
    "document_hash": "abc123...",
    "signed_at": "2024-01-20T10:30:00Z"
  }
}
```

### Verify Integrity
```http
GET /compliance/verify/{batch}/{form}
```

Response:
```json
{
  "success": true,
  "data": {
    "verified": true,
    "signed_by": "John Doe",
    "signed_at": "2024-01-20T10:30:00Z",
    "document_hash": "abc123..."
  }
}
```

### Get Signature Details
```http
GET /compliance/signature/{batch}/{form}
```

Response:
```json
{
  "success": true,
  "data": {
    "signatory_name": "John Doe",
    "signatory_designation": "Factory Manager",
    "signature_type": "DRAWN",
    "signed_at": "2024-01-20T10:30:00Z",
    "ip_address": "192.168.1.1"
  }
}
```

### Lock Batch
```http
POST /compliance/batch/{batch}/lock
```

### Unlock Batch (Admin Only)
```http
POST /compliance/batch/{batch}/unlock
```

## Signature Types

### 1. DRAWN Signature
Canvas-based signature capture:
- User draws signature on HTML5 canvas
- Captured as base64 PNG
- Stored securely in `compliance/signatures/{tenant}/{batch}/`

### 2. IMAGE Signature
Upload signature image:
- Validate MIME type (PNG, JPG)
- Resize to standard dimensions
- Store securely

### 3. DIGITAL_CERT (Future)
PKCS12 certificate-based:
- Certificate metadata stored
- Ready for integration with digital certificate providers

## Commands

### Verify All Signatures
```bash
php artisan compliance:verify-signatures
```

Output:
```
═══════════════════════════════════════════════════════
  COMPLIANCE SIGNATURE VERIFICATION
═══════════════════════════════════════════════════════

Checking: Batch 123 - FORM_10
  ✅ VERIFIED
     Signed by: John Doe
     Signed at: 2024-01-20 10:30:00

Checking: Batch 123 - FORM_B
  ❌ INTEGRITY VIOLATION
     Error: DOCUMENT INTEGRITY VIOLATED
     Expected: abc123...
     Actual: def456...

═══════════════════════════════════════════════════════
  VERIFICATION SUMMARY
═══════════════════════════════════════════════════════
  Total Signatures: 36
  ✅ Verified: 35
  ❌ Violated: 1
```

### Verify Specific Tenant
```bash
php artisan compliance:verify-signatures --tenant=4
```

### Verify Specific Batch
```bash
php artisan compliance:verify-signatures --batch=123
```

## Workflow

### 1. Generate Forms
```bash
php artisan compliance:test-generation --all
```

### 2. Sign Form
```javascript
fetch('/compliance/sign/123/FORM_10', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    signatory_name: 'John Doe',
    signatory_designation: 'Factory Manager',
    signature_type: 'DRAWN',
    signature_data: canvasData
  })
});
```

### 3. Lock Batch
```javascript
fetch('/compliance/batch/123/lock', { method: 'POST' });
```

### 4. Verify Integrity
```bash
php artisan compliance:verify-signatures --batch=123
```

### 5. Download Signed Forms
Forms downloaded with integrity check automatically performed.

## Legal Compliance

### Tamil Nadu Statutory Requirements

Signature block includes:
- Manager/Occupier name
- Designation
- Signature
- Date and time
- Place (from branch address)

### Audit Trail

All actions logged with:
- User identity
- IP address
- Timestamp
- User agent
- Action metadata

### Tamper Evidence

Any modification detected:
- Logged as INTEGRITY_VIOLATION
- Alert generated
- Document marked as compromised

## Security Best Practices

### 1. Storage
- Signatures stored outside public directory
- Use `Storage::disk('local')` with restricted access
- No direct URL access

### 2. Validation
- Tenant isolation enforced
- Branch ownership validated
- User authorization checked
- Batch lock status verified

### 3. Transactions
All signing operations wrapped in DB transactions:
```php
DB::beginTransaction();
try {
    // Sign form
    // Log audit
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### 4. Logging
Every action logged:
- Success and failure
- IP address captured
- User agent recorded
- Metadata stored

## Error Handling

All errors throw exceptions with clear messages:
```
"Batch 123 is locked and cannot be modified"
"Form FORM_10 in batch 123 is already signed"
"Document not found: path/to/file.pdf"
"DOCUMENT INTEGRITY VIOLATED"
```

## Integration Points

### With Form Generator
- Document hash computed after PDF generation
- Hash stored before signing
- Verified on every download

### With Batch Processing
- Locked batches cannot be reprocessed
- Prevents accidental regeneration
- Maintains signature integrity

### With Audit System
- All actions logged
- Complete audit trail
- Compliance reporting

## Future Enhancements

### Phase 2
- PDF signature overlay (visual signature on PDF)
- QR code with verification URL
- Timestamp authority integration

### Phase 3
- PKCS12 digital certificate support
- Blockchain anchor hashing
- Period lock (monthly compliance freeze)

### Phase 4
- Multi-signature support
- Approval workflow
- Signature delegation

## Result

**ENTERPRISE-GRADE DIGITAL SIGNATURE SYSTEM**

- Tamper detection with SHA256 hashing
- Complete audit trail
- Multi-tenant secure
- Legal compliance ready
- Production-grade integrity verification
