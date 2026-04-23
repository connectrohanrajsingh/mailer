<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ComposeEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\OutboxController;
use Illuminate\Support\Facades\Route;


Route::middleware(["auth"])
    ->group(function () {

        Route::controller(DashboardController::class)
            ->group(function () {
                Route::get('/', 'index')->name('dashboard.index');
            });

        Route::controller(InboxController::class)
            ->prefix('inbox')
            ->group(function () {
                Route::get('/', 'index')->name('inbox.index');
                Route::get('/show/{emailId}', 'show')->name('inbox.show');
                Route::post('/filter', 'filter')->name('inbox.filter');
            });

        Route::controller(OutboxController::class)
            ->prefix('outbox')
            ->group(function () {
                Route::get('/', 'index')->name('outbox.index');
                Route::post('/filter', 'filter')->name('outbox.filter');
                Route::get('/show/{emailId}', 'show')->name('outbox.show');
            });

        Route::controller(ComposeEmailController::class)
            ->prefix('compose')
            ->group(function () {
                Route::get('/{emailId?}', 'index')->name('compose.index');
                Route::post('/store', 'store')->name('compose.store');
            });

        Route::controller(AttachmentController::class)
            ->prefix('attachment')
            ->group(function () {
                Route::get('/show/{emailBox}/{attachmentId}', 'show')->name('attachment.show');
                Route::get('/download/{emailBox}/{attachmentId}', 'download')->name('attachment.download');
            });
    });