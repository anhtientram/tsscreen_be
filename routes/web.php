<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogViewerController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/logs/app', [LogViewerController::class, 'index']);
