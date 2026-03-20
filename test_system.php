<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\Branch;
use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceSection;
use App\Models\User;

echo "=== SYSTEM ANALYSIS ===\n\n";

echo "1. Tenants: " . Tenant::count() . "\n";
echo "2. Branches: " . Branch::count() . "\n";
echo "3. Forms Master: " . ComplianceFormsMaster::count() . "\n";
echo "4. Sections: " . ComplianceSection::count() . "\n";
echo "5. Users: " . User::count() . "\n";

if (Tenant::count() > 0) {
    $tenant = Tenant::first();
    echo "\nFirst Tenant: " . $tenant->name . " (ID: " . $tenant->id . ")\n";
    echo "Subscription: " . $tenant->subscription_type . "\n";
}

if (Branch::count() > 0) {
    $branch = Branch::first();
    echo "\nFirst Branch: " . $branch->branch_name . " (ID: " . $branch->id . ")\n";
}

if (ComplianceFormsMaster::count() > 0) {
    echo "\nForms by Frequency:\n";
    $forms = ComplianceFormsMaster::where('is_active', true)->get();
    foreach ($forms->groupBy('frequency') as $freq => $group) {
        echo "  $freq: " . $group->count() . "\n";
    }
}

echo "\n=== END ANALYSIS ===\n";
?>
