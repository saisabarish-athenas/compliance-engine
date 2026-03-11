<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceExecutionController;
use App\Http\Controllers\Compliance\ProjectSettingsController;
use App\Http\Controllers\Compliance\SignatureController;
use App\Http\Controllers\ManualDataController;
use App\Http\Controllers\ManualUploadController;
use App\Http\Controllers\Compliance\CompliancePreviewController;
use App\Http\Controllers\Compliance\ComplianceTestAnalysisController;
use App\Http\Controllers\Compliance\ComplianceDiagnosticController;

Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [ComplianceExecutionController::class, 'dashboard'])->name('compliance.dashboard');
    Route::get('/dashboard/testanalysisreport', [ComplianceTestAnalysisController::class, 'testAnalysisReport'])->name('compliance.testanalysis');
    
    // Diagnostic routes
    Route::get('/diagnostics/run', [ComplianceDiagnosticController::class, 'runDiagnostics'])->name('compliance.diagnostics.run');
    Route::get('/diagnostics/latest', [ComplianceDiagnosticController::class, 'getLatestReport'])->name('compliance.diagnostics.latest');
    Route::get('/diagnostics/dashboard', [ComplianceDiagnosticController::class, 'getDashboardData'])->name('compliance.diagnostics.dashboard');
    
    Route::get('/forms/{section}', [ComplianceExecutionController::class, 'forms'])->name('compliance.forms');
    Route::post('/batch/create', [ComplianceExecutionController::class, 'createBatch'])->name('compliance.batch.create');
    Route::get('/batch/{id}/download', [ComplianceExecutionController::class, 'download'])->name('compliance.batch.download');
    Route::post('/form/upload/{batch}/{form}', [ComplianceExecutionController::class, 'uploadForm'])->name('compliance.form.upload');
    Route::post('/batch/{batch}/process-uploads', [ManualUploadController::class, 'processManualUploads'])->name('compliance.batch.processUploads');

    // Orchestrator routes
    Route::get('/orchestrator', [\App\Http\Controllers\Compliance\ComplianceOrchestratorController::class, 'dashboard'])->name('compliance.orchestrator.dashboard');
    Route::post('/orchestrator/run', [\App\Http\Controllers\Compliance\ComplianceOrchestratorController::class, 'run'])->name('compliance.orchestrator.run');
    Route::get('/orchestrator/logs', [\App\Http\Controllers\Compliance\ComplianceOrchestratorController::class, 'logs'])->name('compliance.orchestrator.logs');
    Route::get('/orchestrator/stats', [\App\Http\Controllers\Compliance\ComplianceOrchestratorController::class, 'stats'])->name('compliance.orchestrator.stats');

    Route::get('/settings', [ProjectSettingsController::class, 'index'])->name('compliance.settings');
    Route::post('/settings', [ProjectSettingsController::class, 'update'])->name('compliance.settings.update');

    // MINIMAL subscription routes - Manual data entry
    Route::get('/manual-data/{month}/{year}', [ManualDataController::class, 'show'])->name('compliance.manual-data.show');
    Route::post('/manual-data/{month}/{year}', [ManualDataController::class, 'save'])->name('compliance.manual-data.save');
    Route::post('/batch/{batch}/upload-csv', [ComplianceExecutionController::class, 'uploadDataFile'])->name('compliance.batch.uploadCsv');

    // Batch processing - Available for both MINIMAL and FULL
    Route::post('/batch/process/{id}', [ComplianceExecutionController::class, 'processBatch'])->name('compliance.batch.process');

    // Universal Preview - Works for ALL forms automatically
    Route::get('/preview/{formCode}', [CompliancePreviewController::class, 'preview'])->name('compliance.preview');

    // Preview available for both MINIMAL and FULL
    Route::get('/batch/{batch}/preview/{form}', [ComplianceExecutionController::class, 'previewForm'])->name('compliance.batch.preview');
    
    // Re-audit route
    Route::post('/batch/{batch}/re-audit/{form}', [ComplianceExecutionController::class, 'reAudit'])->name('compliance.batch.reAudit');
    
    // Fix violations routes
    Route::post('/batch/{batch}/fix-violations/{form}', [ComplianceExecutionController::class, 'fixViolations'])->name('compliance.batch.fixViolations');
    Route::post('/batch/{batch}/submit-fix/{form}', [ComplianceExecutionController::class, 'submitFix'])->name('compliance.batch.submitFix');

    // Certification routes
    Route::post('/batch/{batch}/certify', [ComplianceExecutionController::class, 'certifyBatch'])->name('compliance.batch.certify');
    Route::get('/batch/{batch}/certification-status', [ComplianceExecutionController::class, 'getCertificationStatus'])->name('compliance.batch.certificationStatus');

    // FULL subscription routes - STRICT ENFORCEMENT
    Route::middleware(['auth'])->group(function () {
        Route::get('/batch/{batch}/form/{form}/refresh', [ComplianceExecutionController::class, 'refreshFormData'])->name('compliance.form.refresh');
        Route::get('/batch/{batch}/inspection-pack', [ComplianceExecutionController::class, 'downloadInspectionPack'])->name('compliance.batch.inspectionPack');

        // Digital Signature Routes
        Route::post('/sign/{batch}/{form}', [SignatureController::class, 'sign'])->name('compliance.sign');
        Route::get('/verify/{batch}/{form}', [SignatureController::class, 'verify'])->name('compliance.verify');
        Route::get('/signature/{batch}/{form}', [SignatureController::class, 'getDetails'])->name('compliance.signature.details');
        Route::post('/batch/{batch}/lock', [SignatureController::class, 'lockBatch'])->name('compliance.batch.lock');
        Route::post('/batch/{batch}/unlock', [SignatureController::class, 'unlockBatch'])->name('compliance.batch.unlock');
    });
});
