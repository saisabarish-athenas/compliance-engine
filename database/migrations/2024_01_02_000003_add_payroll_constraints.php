<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename payroll_cycles to workforce_payroll_cycle if needed
        if (Schema::hasTable('payroll_cycles') && !Schema::hasTable('workforce_payroll_cycle')) {
            Schema::rename('payroll_cycles', 'workforce_payroll_cycle');
        }

        // Rename payroll_entries to workforce_payroll_entry if needed
        if (Schema::hasTable('payroll_entries') && !Schema::hasTable('workforce_payroll_entry')) {
            Schema::rename('payroll_entries', 'workforce_payroll_entry');
        }

        // Add unique constraint on workforce_payroll_cycle
        if (Schema::hasTable('workforce_payroll_cycle')) {
            Schema::table('workforce_payroll_cycle', function (Blueprint $table) {
                $table->unique(['tenant_id', 'cycle_name'], 'unique_tenant_cycle');
            });
        }

        // Add constraints and indexes on workforce_payroll_entry
        if (Schema::hasTable('workforce_payroll_entry')) {
            Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                $table->unique(['payroll_cycle_id', 'employee_id'], 'unique_cycle_employee');
                $table->index(['tenant_id', 'payroll_cycle_id']);
                
                if (!Schema::hasColumn('workforce_payroll_entry', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->after('id')->index();
                }
            });

            // Add foreign keys if not exists
            Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                $table->foreign('payroll_cycle_id')->references('id')->on('workforce_payroll_cycle')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('workforce_employee')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workforce_payroll_entry')) {
            Schema::table('workforce_payroll_entry', function (Blueprint $table) {
                $table->dropForeign(['payroll_cycle_id']);
                $table->dropForeign(['employee_id']);
                $table->dropUnique('unique_cycle_employee');
                $table->dropIndex(['tenant_id', 'payroll_cycle_id']);
            });
        }

        if (Schema::hasTable('workforce_payroll_cycle')) {
            Schema::table('workforce_payroll_cycle', function (Blueprint $table) {
                $table->dropUnique('unique_tenant_cycle');
            });
        }

        if (Schema::hasTable('workforce_payroll_entry')) {
            Schema::rename('workforce_payroll_entry', 'payroll_entries');
        }

        if (Schema::hasTable('workforce_payroll_cycle')) {
            Schema::rename('workforce_payroll_cycle', 'payroll_cycles');
        }
    }
};
