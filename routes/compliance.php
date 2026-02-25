<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceExecutionController;
use App\Http\Controllers\Compliance\ProjectSettingsController;
use App\Http\Controllers\Compliance\SignatureController;

Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [ComplianceExecutionController::class, 'dashboard'])->name('compliance.dashboard');
    Route::get('/forms/{section}', [ComplianceExecutionController::class, 'forms'])->name('compliance.forms');
    Route::post('/batch/create', [ComplianceExecutionController::class, 'createBatch'])->name('compliance.batch.create');
    Route::get('/batch/{id}/download', [ComplianceExecutionController::class, 'download'])->name('compliance.batch.download');
    Route::post('/form/upload/{batch}/{form}', [ComplianceExecutionController::class, 'uploadForm'])->name('compliance.form.upload');
    Route::post('/batch/{batch}/process-uploads', [\App\Http\Controllers\ManualUploadController::class, 'processManualUploads'])->name('compliance.batch.processUploads');

    Route::get('/settings', [ProjectSettingsController::class, 'index'])->name('compliance.settings');
    Route::post('/settings', [ProjectSettingsController::class, 'update'])->name('compliance.settings.update');

    // FULL subscription routes - STRICT ENFORCEMENT
    Route::middleware(['subscription.full'])->group(function () {
        Route::post('/batch/process/{id}', [ComplianceExecutionController::class, 'processBatch'])->name('compliance.batch.process');
        Route::get('/batch/{batch}/preview/{form}', [ComplianceExecutionController::class, 'previewForm'])->name('compliance.batch.preview');
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
