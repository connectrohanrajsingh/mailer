<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboxController;
use Illuminate\Support\Facades\Route;


Route::controller(DashboardController::class)
    ->group(function () {
        Route::get('/', 'index')->name('dashboard.index');
    });

Route::controller(InboxController::class)
    ->prefix('inbox')
    ->group(function () {
        Route::get('/', 'index')->name('inbox.index');
    });
