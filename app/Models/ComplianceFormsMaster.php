<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceFormsMaster extends Model
{
    protected $table = 'compliance_forms_master';

    protected $fillable = [
        'form_code',
        'form_name',
        'act_type',
        'frequency',
        'due_day',
        'due_month',
        'grace_days',
        'priority',
        'auto_generate',
        'upload_only',
        'is_active',
    ];

    protected $casts = [
        'auto_generate' => 'boolean',
        'upload_only' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function complianceStatuses(): HasMany
    {
        return $this->hasMany(ComplianceStatus::class, 'form_id');
    }

    public function generationLogs(): HasMany
    {
        return $this->hasMany(ComplianceGenerationLog::class, 'form_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(ComplianceReminder::class, 'form_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplianceAttachment::class, 'form_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ComplianceFormSource::class, 'form_id');
    }
}
