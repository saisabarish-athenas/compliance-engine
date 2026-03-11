<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixMissingComplianceColumns extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workforce_advances')) {
            Schema::table('workforce_advances', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_advances', 'last_month')) {
                    $table->date('last_month')->nullable();
                }
            });
        }

        if (Schema::hasTable('workforce_deductions')) {
            Schema::table('workforce_deductions', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_deductions', 'deduction_month')) {
                    $table->date('deduction_month')->nullable();
                }
            });
        }

        if (Schema::hasTable('workforce_fines')) {
            Schema::table('workforce_fines', function (Blueprint $table) {
                if (!Schema::hasColumn('workforce_fines', 'fine_month')) {
                    $table->date('fine_month')->nullable();
                }
            });
        }
    }
    public function down(): void
    {
        Schema::table('contract_labour_deployment', function (Blueprint $table) {
            $table->dropColumn(['nature_of_work', 'work_location', 'termination_reason']);
        });

        Schema::table('workforce_advances', function (Blueprint $table) {
            $table->dropColumn('last_month');
        });
    }
}
