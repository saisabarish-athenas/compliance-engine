<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('tenant_id')->index();
                $table->string('branch_name');
                $table->string('factory_license_number')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('compliance_status') && !Schema::hasColumn('compliance_status', 'branch_id')) {
            Schema::table('compliance_status', function (Blueprint $table) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('compliance_status')) {
            Schema::table('compliance_status', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
        }

        Schema::dropIfExists('branches');
    }
};
