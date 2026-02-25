<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractLabourDeployment extends Model
{
    use SoftDeletes;

    protected $table = 'contract_labour_deployment';

    protected $fillable = [
        'tenant_id',
        'contractor_compliance_id',
        'employee_id',
        'project_id',
        'branch_id',
        'wage_rate',
        'deployment_start',
        'deployment_end',
        'work_order_number',
        'work_order_date',
        'status',
    ];

    protected $casts = [
        'wage_rate' => 'decimal:2',
        'deployment_start' => 'date',
        'deployment_end' => 'date',
        'work_order_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contractorCompliance(): BelongsTo
    {
        return $this->belongsTo(ContractorCompliance::class, 'contractor_compliance_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(WorkforceEmployee::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor_compliance_id');
    }
}
