<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StaffMemberController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/preferences/per-page', [PreferenceController::class, 'setPerPage'])->name('preferences.per-page');

    Route::post('/branches/switch', [BranchController::class, 'switch'])->name('branches.switch');
    Route::resource('branches', BranchController::class)->except(['show', 'destroy'])->middleware('admin');

    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('admin');
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active')->middleware('admin');

    Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
    Route::resource('items', ItemController::class)->except(['destroy']);

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock', [StockController::class, 'store'])->name('stock.store');

    Route::resource('batches', BatchController::class)->only(['index', 'show']);

    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::patch('/customers/{customer}/toggle-active', [CustomerController::class, 'toggleActive'])->name('customers.toggle-active');
    Route::get('/customers/{customer}/statement-pdf', [CustomerController::class, 'statementPdf'])->name('customers.statement-pdf');
    Route::post('/customers/{customer}/payments', [CustomerController::class, 'storePayment'])->name('customers.payments.store');
    Route::resource('customers', CustomerController::class);

    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
    Route::resource('suppliers', SupplierController::class)->except(['destroy']);

    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/purchases/{purchase}/payments', [PurchaseController::class, 'storePayment'])->name('purchases.payments.store');
    Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');

    Route::patch('/staff/{staffMember}/toggle-active', [StaffMemberController::class, 'toggleActive'])->name('staff.toggle-active');
    Route::resource('staff', StaffMemberController::class)->except(['show', 'destroy'])->parameters(['staff' => 'staffMember']);

    Route::resource('expenses', ExpenseController::class)->except(['show', 'destroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
