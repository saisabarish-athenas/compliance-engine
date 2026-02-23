<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkforcePayrollCycle extends Model
{
    use SoftDeletes;

    protected $table = 'workforce_payroll_cycle';

    protected $fillable = [
        'tenant_id',
        'cycle_name',
        'period_from',
        'period_to',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'processed_at' => 'datetime',
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
        return $this->hasMany(WorkforcePayrollEntry::class, 'payroll_cycle_id');
    }
}
