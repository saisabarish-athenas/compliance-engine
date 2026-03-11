<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Compliance\Repositories\{
    EmployeeRepository,
    PayrollRepository,
    AttendanceRepository,
    ContractorRepository,
    IncidentRepository,
    BonusRepository,
    DeductionRepository,
};
use App\Compliance\ComplianceDataService;

class ComplianceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EmployeeRepository::class);
        $this->app->singleton(PayrollRepository::class);
        $this->app->singleton(AttendanceRepository::class);
        $this->app->singleton(ContractorRepository::class);
        $this->app->singleton(IncidentRepository::class);
        $this->app->singleton(BonusRepository::class);
        $this->app->singleton(DeductionRepository::class);
        $this->app->singleton(ComplianceDataService::class);
        $this->app->singleton(\App\Services\Compliance\ComplianceOrchestrator::class);
    }

    public function boot(): void
    {
        //
    }
}
