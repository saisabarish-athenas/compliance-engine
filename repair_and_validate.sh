#!/bin/bash

echo "=========================================="
echo "COMPLIANCE ENGINE REPAIR & VALIDATION"
echo "=========================================="
echo ""

# Step 1: Run migrations
echo "Step 1: Running database migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo "✓ Migrations completed successfully"
else
    echo "✗ Migration failed"
    exit 1
fi
echo ""

# Step 2: Seed demo data
echo "Step 2: Seeding demo data..."
php artisan db:seed --class=ComplianceFormsDemoSeeder
if [ $? -eq 0 ]; then
    echo "✓ Demo data seeded successfully"
else
    echo "✗ Seeding failed"
    exit 1
fi
echo ""

# Step 3: Validate forms
echo "Step 3: Validating form rendering..."
php artisan tinker << 'EOF'
$forms = ['FORM_XII', 'FORM_XIII', 'FORM_XVI', 'FORM_XX', 'FORM_XXI', 'FORM_XXII'];
$tenantId = DB::table('tenants')->first()->id;
$branchId = DB::table('branches')->where('tenant_id', $tenantId)->first()->id;

foreach ($forms as $form) {
    try {
        $service = match($form) {
            'FORM_XII' => new \App\Services\Compliance\Forms\FormXIIService(),
            'FORM_XIII' => new \App\Services\Compliance\Forms\FormXIIIService(),
            'FORM_XVI' => new \App\Services\Compliance\Forms\FormXVIService(),
            'FORM_XX' => new \App\Services\Compliance\Forms\FormXXService(),
            'FORM_XXI' => new \App\Services\Compliance\Forms\FormXXIService(),
            'FORM_XXII' => new \App\Services\Compliance\Forms\FormXXIIService(),
        };
        
        $data = $service->generate($tenantId, $branchId, 1, 2025);
        $rowCount = count($data['rows'] ?? []);
        echo "✓ $form: $rowCount records\n";
    } catch (\Exception $e) {
        echo "✗ $form: " . $e->getMessage() . "\n";
    }
}
EOF

echo ""
echo "=========================================="
echo "REPAIR COMPLETE"
echo "=========================================="
