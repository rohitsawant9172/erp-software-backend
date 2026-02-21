<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Health check (no auth required — used by Docker HEALTHCHECK + platform monitors)
Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

Route::middleware(['auth:api', 'viewer.readonly'])->group(function () {

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::apiResource('farmers', FarmerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceActionController::class, 'show']);
    Route::get('/invoices/{invoice}/tally', [\App\Http\Controllers\InvoiceActionController::class, 'tally']);
    Route::post('/invoices/{invoice}/tally/sync', [\App\Http\Controllers\InvoiceActionController::class, 'syncTally']);
    Route::apiResource('payments', PaymentController::class);
    
    // Custom routes
    Route::get('/reports/sales', [InvoiceController::class, 'salesReport']);
    Route::get('/reports/stock', [ProductController::class, 'stockReport']);
    Route::post('/invoices/{invoice}/pay', [PaymentController::class, 'payInvoice']);

    // New modules
    Route::apiResource('godowns', \App\Http\Controllers\GodownController::class);
    Route::apiResource('batches', \App\Http\Controllers\BatchInventoryController::class);
    Route::apiResource('users', \App\Http\Controllers\UserController::class)->except(['show']);
});

Route::get('/invoices/{invoice}/print', [\App\Http\Controllers\InvoicePrintController::class, 'print']);
