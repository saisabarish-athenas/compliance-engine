<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        // Add other employee fields as per your schema
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

    public function payrollEntries(): HasMany
    {
        return $this->hasMany(WorkforcePayrollEntry::class);
    }

    public function bonusRecords(): HasMany
    {
        return $this->hasMany(BonusRecord::class);
    }

    public function contractLabourDeployments(): HasMany
    {
        return $this->hasMany(ContractLabourDeployment::class);
    }

    public function incidentDocuments(): HasMany
    {
        return $this->hasMany(IncidentDocument::class);
    }
}
