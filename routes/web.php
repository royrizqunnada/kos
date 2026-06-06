<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('rooms', RoomController::class);
    Route::resource('tenants', TenantController::class);

    Route::get('leases', [LeaseController::class, 'index'])->name('leases.index');
    Route::get('leases/create', [LeaseController::class, 'create'])->name('leases.create');
    Route::post('leases', [LeaseController::class, 'store'])->name('leases.store');
    Route::get('leases/{lease}/edit', [LeaseController::class, 'edit'])->name('leases.edit');
    Route::put('leases/{lease}', [LeaseController::class, 'update'])->name('leases.update');
    Route::get('leases/{lease}', [LeaseController::class, 'show'])->name('leases.show');
    Route::post('leases/{lease}/end', [LeaseController::class, 'end'])->name('leases.end');
    Route::delete('leases/{lease}', [LeaseController::class, 'destroy'])->name('leases.destroy');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('reports', ReportController::class)->name('reports.index');
    Route::get('reports/export/csv', [ReportExportController::class, 'csv'])->name('reports.csv');
    Route::get('reports/print', [ReportExportController::class, 'print'])->name('reports.print');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});
