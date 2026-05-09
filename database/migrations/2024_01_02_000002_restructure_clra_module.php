<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename contractors to contractor_master if exists
        if (Schema::hasTable('contractors') && !Schema::hasTable('contractor_master')) {
            Schema::rename('contractors', 'contractor_master');
        }

        // Update contractor_master structure
        if (Schema::hasTable('contractor_master')) {
            Schema::table('contractor_master', function (Blueprint $table) {
                if (!Schema::hasColumn('contractor_master', 'company_type')) {
                    $table->string('company_type')->nullable()->after('tenant_id');
                }
                if (!Schema::hasColumn('contractor_master', 'company_name')) {
                    $table->renameColumn('contractor_name', 'company_name');
                }
                if (!Schema::hasColumn('contractor_master', 'company_address')) {
                    $table->text('company_address')->nullable()->after('company_name');
                }
                if (!Schema::hasColumn('contractor_master', 'contact_person')) {
                    $table->string('contact_person')->nullable()->after('company_address');
                }
                if (!Schema::hasColumn('contractor_master', 'contact_number')) {
                    $table->string('contact_number')->nullable()->after('contact_person');
                }
                if (!Schema::hasColumn('contractor_master', 'email')) {
                    $table->string('email')->nullable()->after('contact_number');
                }
                if (!Schema::hasColumn('contractor_master', 'pan_number')) {
                    $table->string('pan_number')->nullable()->after('email');
                }
                if (!Schema::hasColumn('contractor_master', 'gst_number')) {
                    $table->string('gst_number')->nullable()->after('pan_number');
                }
                if (!Schema::hasColumn('contractor_master', 'status')) {
                    $table->string('status')->default('active')->after('gst_number');
                }
            });
        }

        // Create contractor_compliance table
        if (!Schema::hasTable('contractor_compliance')) {
            Schema::create('contractor_compliance', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('contractor_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('clra_license_number')->nullable();
                $table->date('license_valid_from')->nullable();
                $table->date('license_valid_to')->nullable();
                $table->integer('max_worker_limit')->nullable();
                $table->string('pf_code')->nullable();
                $table->string('esi_code')->nullable();
                $table->string('labour_registration_number')->nullable();
                $table->date('last_return_filed')->nullable();
                $table->boolean('is_compliant')->default(true);
                $table->text('compliance_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('contractor_id')->references('id')->on('contractor_master')->onDelete('cascade');
                $table->index(['contractor_id', 'branch_id']);
            });
        }

        // Rename contract_labour to contract_labour_deployment if exists
        if (Schema::hasTable('contract_labour') && !Schema::hasTable('contract_labour_deployment')) {
            Schema::rename('contract_labour', 'contract_labour_deployment');
        }

        // Update contract_labour_deployment structure
        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                if (!Schema::hasColumn('contract_labour_deployment', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->after('id')->index();
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'contractor_compliance_id')) {
                    $table->unsignedBigInteger('contractor_compliance_id')->nullable()->after('tenant_id');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'project_id')) {
                    $table->unsignedBigInteger('project_id')->nullable()->after('contractor_compliance_id');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('project_id');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'deployment_start')) {
                    $table->renameColumn('employment_start', 'deployment_start');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'deployment_end')) {
                    $table->renameColumn('employment_end', 'deployment_end');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'work_order_number')) {
                    $table->string('work_order_number')->nullable()->after('deployment_end');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'work_order_date')) {
                    $table->date('work_order_date')->nullable()->after('work_order_number');
                }
                if (!Schema::hasColumn('contract_labour_deployment', 'status')) {
                    $table->string('status')->default('active')->after('work_order_date');
                }
            });

            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->foreign('contractor_compliance_id')->references('id')->on('contractor_compliance')->onDelete('cascade');
                $table->index(['tenant_id', 'project_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::table('contract_labour_deployment', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropForeign(['contractor_compliance_id']);
                $table->dropIndex(['tenant_id', 'project_id']);
            });
        }

        Schema::dropIfExists('contractor_compliance');

        if (Schema::hasTable('contract_labour_deployment')) {
            Schema::rename('contract_labour_deployment', 'contract_labour');
        }

        if (Schema::hasTable('contractor_master')) {
            Schema::rename('contractor_master', 'contractors');
        }
    }
};
