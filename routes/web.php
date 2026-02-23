<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/compliance.php';

Route::get('/', function () {
    return view('welcome');
});

Route::get('/compliance-dashboard', function () {
    return view('compliance.dashboard');
});
