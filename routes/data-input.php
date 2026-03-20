<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataInputController;

Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    Route::post('/batch/{batch}/save-manual-data', [DataInputController::class, 'saveManualData'])->name('data.save-manual');
    Route::post('/form/upload/{batch}/{form}', [DataInputController::class, 'uploadPdfForm'])->name('data.upload-pdf');
    Route::post('/batch/{batch}/upload-csv', [DataInputController::class, 'uploadCsvData'])->name('data.upload-csv');
});
