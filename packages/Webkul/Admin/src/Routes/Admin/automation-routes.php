<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Settings\Automation\AutomationController;

/**
 * Automation Routes.
 */
Route::controller(AutomationController::class)->prefix('automation')->group(function () {
    Route::get('', 'index')->name('admin.settings.automation.index');
    Route::get('temperature', 'temperature')->name('admin.settings.automation.temperature');
    Route::get('logs', 'logs')->name('admin.settings.automation.logs');
    Route::post('store', 'store')->name('admin.settings.automation.store');
    Route::get('edit/{id}', 'edit')->name('admin.settings.automation.edit');
    Route::put('update/{id}', 'update')->name('admin.settings.automation.update');
    Route::delete('destroy/{id}', 'destroy')->name('admin.settings.automation.destroy');
    Route::post('toggle/{id}', 'toggle')->name('admin.settings.automation.toggle');
});
