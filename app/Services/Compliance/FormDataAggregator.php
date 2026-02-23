<?php

namespace App\Services\Compliance;

use App\Models\ComplianceFormsMaster;
use App\Models\ComplianceFormSource;
use App\Models\WorkforcePayrollEntry;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class FormDataAggregator
{
    public function aggregateData(ComplianceFormsMaster $form, string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $sources = $form->sources;
        
        if ($sources->isEmpty()) {
            return match($form->form_code) {
                'FORM_A', 'FORM_B' => $this->aggregateWageRegister($periodFrom, $periodTo, $branchId),
                'FORM_C' => $this->aggregateOTRegister($periodFrom, $periodTo, $branchId),
                'CLRA_WAGE' => $this->aggregateCLRAWage($periodFrom, $periodTo, $branchId),
                'BONUS_REGISTER' => $this->aggregateBonus($periodFrom, $periodTo, $branchId),
                'ATTENDANCE_REGISTER' => $this->aggregateAttendance($periodFrom, $periodTo, $branchId),
                default => [],
            };
        }
        
        return $this->aggregateFromSources($sources, $periodFrom, $periodTo, $branchId);
    }

    private function aggregateFromSources($sources, string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $data = [];
        
        foreach ($sources as $source) {
            $methodName = 'aggregate' . str_replace('_', '', ucwords($source->source_type, '_'));
            
            if (method_exists($this, $methodName)) {
                $data[$source->source_type] = $this->$methodName($periodFrom, $periodTo, $branchId);
            }
        }
        
        return $data;
    }

    public function aggregateWageRegister(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $query = WorkforcePayrollEntry::with('employee')
            ->whereHas('payrollCycle', function ($q) use ($periodFrom, $periodTo) {
                $q->where('period_from', '>=', $periodFrom)
                  ->where('period_to', '<=', $periodTo);
            });

        if ($branchId) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
        }

        $entries = $query->get();

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_employees' => $entries->count(),
            'total_wages' => $entries->sum('gross_salary'),
            'total_deductions' => $entries->sum('total_deductions'),
            'net_payable' => $entries->sum('net_salary'),
            'entries' => $entries->map(fn($e) => [
                'employee_code' => $e->employee->employee_code ?? '',
                'employee_name' => $e->employee->name ?? '',
                'basic' => $e->basic_earned,
                'da' => $e->da_earned,
                'hra' => $e->hra_earned,
                'gross' => $e->gross_salary,
                'pf' => $e->pf_employee,
                'esi' => $e->esi_employee,
                'net' => $e->net_salary,
            ])->toArray(),
        ];
    }

    public function aggregateOTRegister(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $query = WorkforcePayrollEntry::with('employee')
            ->whereHas('payrollCycle', function ($q) use ($periodFrom, $periodTo) {
                $q->whereBetween('period_from', [$periodFrom, $periodTo]);
            })
            ->where('overtime_hours', '>', 0);

        if ($branchId) {
            $query->whereHas('employee', fn($q) => $q->where('branch_id', $branchId));
        }

        $entries = $query->get();

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_ot_hours' => $entries->sum('overtime_hours'),
            'total_ot_wages' => $entries->sum('overtime_wages'),
            'entries' => $entries->map(fn($e) => [
                'employee_code' => $e->employee->employee_code ?? '',
                'employee_name' => $e->employee->name ?? '',
                'ot_hours' => $e->overtime_hours,
                'ot_wages' => $e->overtime_wages,
            ])->toArray(),
        ];
    }

    public function aggregateCLRAWage(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $query = DB::table('contract_labour_deployment as cld')
            ->join('workforce_payroll_entry as wpe', 'cld.employee_id', '=', 'wpe.employee_id')
            ->join('workforce_payroll_cycle as wpc', 'wpe.payroll_cycle_id', '=', 'wpc.id')
            ->join('contractor_master as cm', 'cld.contractor_id', '=', 'cm.id')
            ->whereBetween('wpc.period_from', [$periodFrom, $periodTo])
            ->where('cld.tenant_id', auth()->user()->tenant_id);

        if ($branchId) {
            $query->where('cld.branch_id', $branchId);
        }

        $entries = $query->select([
            'cm.company_name as contractor_name',
            'wpe.employee_id',
            'wpe.gross_salary',
            'wpe.net_salary',
            'cld.wage_rate',
        ])->get();

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_contract_workers' => $entries->count(),
            'total_wages' => $entries->sum('net_salary'),
            'entries' => $entries->toArray(),
        ];
    }

    public function aggregateBonus(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $query = DB::table('workforce_bonus_record as wbr')
            ->join('workforce_employee as we', 'wbr.employee_id', '=', 'we.id')
            ->whereBetween('wbr.payment_date', [$periodFrom, $periodTo])
            ->where('wbr.tenant_id', auth()->user()->tenant_id);

        if ($branchId) {
            $query->where('we.branch_id', $branchId);
        }

        $entries = $query->select([
            'we.employee_code',
            'we.name',
            'wbr.bonus_percentage',
            'wbr.bonus_amount',
            'wbr.payment_date',
        ])->get();

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_bonus_paid' => $entries->sum('bonus_amount'),
            'entries' => $entries->toArray(),
        ];
    }

    public function aggregateAttendance(string $periodFrom, string $periodTo, ?int $branchId = null): array
    {
        $query = DB::table('workforce_attendance as wa')
            ->join('workforce_employee as we', 'wa.employee_id', '=', 'we.id')
            ->whereBetween('wa.date', [$periodFrom, $periodTo])
            ->where('wa.tenant_id', auth()->user()->tenant_id);

        if ($branchId) {
            $query->where('we.branch_id', $branchId);
        }

        $entries = $query->select([
            'we.employee_code',
            'we.name',
            'wa.date',
            'wa.status',
            'wa.hours_worked',
        ])->get();

        return [
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'total_records' => $entries->count(),
            'entries' => $entries->toArray(),
        ];
    }
}
