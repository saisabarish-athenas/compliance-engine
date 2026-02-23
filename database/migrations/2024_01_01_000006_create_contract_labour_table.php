<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_labour', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contractor_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->string('deployment_location')->nullable();
            $table->decimal('wage_rate', 12, 2);
            $table->date('employment_start');
            $table->date('employment_end')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contractor_id')->references('id')->on('contractors')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_labour');
    }
};
