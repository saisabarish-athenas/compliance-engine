<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceBatchForm extends Model
{
    protected $table = 'compliance_batch_forms';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'form_code',
        'section',
        'file_path',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(ComplianceExecutionBatch::class, 'batch_id');
    }
}
