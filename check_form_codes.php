<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\ComplianceFormsMaster;

$forms = ComplianceFormsMaster::where('is_active', true)->get();
echo "Form Codes:\n";
foreach ($forms as $form) {
    echo "  " . $form->form_code . "\n";
}
?>
