<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees') && !Schema::hasTable('workforce_employee')) {
            Schema::rename('employees', 'workforce_employee');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_employee') && !Schema::hasTable('employees')) {
            Schema::rename('workforce_employee', 'employees');
        }
    }
};
