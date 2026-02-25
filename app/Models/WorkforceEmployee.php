<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkforceEmployee extends Model
{
    use SoftDeletes;

    protected $table = 'workforce_employee';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'employee_code',
        'name',
        'pf_number',
        'esi_number',
        'date_of_joining',
        'designation',
        'department',
        'basic_salary',
        'status',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
        'basic_salary' => 'decimal:2',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payrollEntries(): HasMany
    {
        return $this->hasMany(WorkforcePayrollEntry::class, 'employee_id');
    }

    public function bonusRecords(): HasMany
    {
        return $this->hasMany(BonusRecord::class, 'employee_id');
    }

    public function contractLabourDeployments(): HasMany
    {
        return $this->hasMany(ContractLabourDeployment::class, 'employee_id');
    }

    public function incidentDocuments(): HasMany
    {
        return $this->hasMany(IncidentDocument::class, 'employee_id');
    }
}
