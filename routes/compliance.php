<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComplianceExecutionController;

Route::prefix('compliance')->group(function () {

    Route::get('/sections', [ComplianceExecutionController::class, 'sections']);
    Route::get('/forms/{section}', [ComplianceExecutionController::class, 'forms']);
    Route::post('/batch/create', [ComplianceExecutionController::class, 'createBatch']);
    Route::post('/batch/process/{id}', [ComplianceExecutionController::class, 'processBatch']);
    Route::get('/batch/{id}/download', [ComplianceExecutionController::class, 'download']);

});
