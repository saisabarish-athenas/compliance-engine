<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payroll_cycle_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            
            $table->integer('total_days_worked');
            $table->integer('paid_leave_days')->default(0);
            $table->integer('unpaid_leave_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            
            $table->decimal('basic_earned', 12, 2);
            $table->decimal('da_earned', 12, 2)->default(0);
            $table->decimal('hra_earned', 12, 2)->default(0);
            $table->decimal('other_allowances', 12, 2)->default(0);
            $table->decimal('overtime_wages', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2);
            
            $table->decimal('pf_employee', 12, 2)->default(0);
            $table->decimal('esi_employee', 12, 2)->default(0);
            $table->decimal('professional_tax', 12, 2)->default(0);
            $table->decimal('fines', 12, 2)->default(0);
            $table->decimal('advances', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            
            $table->decimal('net_salary', 12, 2);
            $table->date('payment_date')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('transaction_reference')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['payroll_cycle_id', 'employee_id']);
            $table->foreign('payroll_cycle_id')->references('id')->on('payroll_cycles')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
