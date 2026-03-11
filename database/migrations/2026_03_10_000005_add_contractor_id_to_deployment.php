<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                if (!Schema::hasColumn('contract_labour_deployment', 'contractor_id')) {
                    $table->unsignedBigInteger('contractor_id')->nullable()->after('tenant_id')->index();
                    $table->foreign('contractor_id')->references('id')->on('contractor_master')->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                if (Schema::hasColumn('contract_labour_deployment', 'contractor_id')) {
                    $table->dropForeign(['contractor_id']);
                    $table->dropColumn('contractor_id');
                }
            });
        }
    }
};
