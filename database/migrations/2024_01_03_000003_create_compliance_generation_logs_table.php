<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_generation_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('form_id')->index();
            $table->unsignedBigInteger('compliance_status_id')->index();
            $table->unsignedBigInteger('generated_by');
            $table->string('file_path');
            $table->string('checksum_hash');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamp('created_at');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('compliance_forms_master')->onDelete('cascade');
            $table->foreign('compliance_status_id')->references('id')->on('compliance_status')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_generation_logs');
    }
};
