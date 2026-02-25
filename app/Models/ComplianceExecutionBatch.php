<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceExecutionBatch extends Model
{
    protected $table = 'compliance_execution_batches';

    protected $fillable = [
        'tenant_id',
        'section_id',
        'period_from',
        'period_to',
        'period_month',
        'period_year',
        'form_ids',
        'branch_id',
        'status',
        'created_by',
        'processed_at',
        'results',
        'generated_report_path',
    ];

    protected $casts = [
        'form_ids' => 'array',
        'results' => 'array',
        'processed_at' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(ComplianceSection::class, 'section_id');
    }
}
