<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_certification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('form_code', 50);
            $table->integer('certification_score')->default(0);
            $table->boolean('certified')->default(false);
            $table->json('violations')->nullable();
            $table->timestamp('certified_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'form_code']);
            $table->index('batch_id');
            $table->index('certified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_certification_logs');
    }
};
