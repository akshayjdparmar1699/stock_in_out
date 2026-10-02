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

// Temporary diagnostic route to see the live container's own filesystem
// state for public/icons without needing shell access on the host (not
// available on the free Render plan) — remove once the icons 404 is
// actually root-caused and fixed.
Route::get('/__debug-icons', function () {
    $publicPath = public_path();
    $iconsPath = public_path('icons');

    return response()->json([
        'public_path' => $publicPath,
        'public_path_exists' => is_dir($publicPath),
        'public_dir_listing' => is_dir($publicPath) ? array_values(array_diff(scandir($publicPath), ['.', '..'])) : null,
        'icons_dir_exists' => is_dir($iconsPath),
        'icons_dir_readable' => is_dir($iconsPath) ? is_readable($iconsPath) : null,
        'icons_files' => is_dir($iconsPath)
            ? collect(array_diff(scandir($iconsPath), ['.', '..']))->map(fn ($f) => [
                'name' => $f,
                'perms' => substr(sprintf('%o', fileperms($iconsPath.'/'.$f)), -4),
                'size' => filesize($iconsPath.'/'.$f),
                'readable' => is_readable($iconsPath.'/'.$f),
            ])->values()
            : null,
        'apache_document_root_env' => getenv('APACHE_DOCUMENT_ROOT'),
        'server_document_root' => $_SERVER['DOCUMENT_ROOT'] ?? null,
        'server_script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? null,
        'apache_default_vhost_conf' => @file_get_contents('/etc/apache2/sites-available/000-default.conf'),
        'apache_laravel_conf' => @file_get_contents('/etc/apache2/conf-enabled/laravel.conf'),
        'apache_ports_conf' => @file_get_contents('/etc/apache2/ports.conf'),
        'apache_alias_conf' => @file_get_contents('/etc/apache2/mods-enabled/alias.conf')
            ?: @file_get_contents('/etc/apache2/mods-available/alias.conf'),
        'apache_mods_enabled' => @scandir('/etc/apache2/mods-enabled'),
        'self_request_test' => (function () {
            $port = getenv('PORT') ?: '10000';
            $ch = curl_init("http://127.0.0.1:{$port}/icons/icon-512.png");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'port_used' => $port,
                'http_code' => $info['http_code'] ?? null,
                'curl_error' => $error ?: null,
                'response_head' => $response ? substr($response, 0, 500) : null,
            ];
        })(),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/preferences/per-page', [PreferenceController::class, 'setPerPage'])->name('preferences.per-page');

    Route::post('/branches/switch', [BranchController::class, 'switch'])->name('branches.switch');
    Route::resource('branches', BranchController::class)->except(['show', 'destroy'])->middleware('admin');

    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('admin');
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active')->middleware('admin');

    Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
    Route::resource('items', ItemController::class);

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
    Route::get('/stock/{movement}/edit', [StockController::class, 'edit'])->name('stock.edit');
    Route::put('/stock/{movement}', [StockController::class, 'update'])->name('stock.update');
    Route::delete('/stock/{movement}', [StockController::class, 'destroy'])->name('stock.destroy');

    Route::resource('batches', BatchController::class)->only(['index', 'show']);

    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::patch('/customers/{customer}/toggle-active', [CustomerController::class, 'toggleActive'])->name('customers.toggle-active');
    Route::get('/customers/{customer}/statement-pdf', [CustomerController::class, 'statementPdf'])->name('customers.statement-pdf');
    Route::post('/customers/{customer}/payments', [CustomerController::class, 'storePayment'])->name('customers.payments.store');
    Route::delete('/customers/{customer}/payments/{key}', [CustomerController::class, 'destroyPayment'])->name('customers.payments.destroy');
    Route::resource('customers', CustomerController::class);

    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('invoices.payments.store');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');
    Route::post('/suppliers/{supplier}/payments', [SupplierController::class, 'storePayment'])->name('suppliers.payments.store');
    Route::delete('/suppliers/{supplier}/payments/{key}', [SupplierController::class, 'destroyPayment'])->name('suppliers.payments.destroy');
    Route::resource('suppliers', SupplierController::class)->except(['destroy']);

    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/purchases/{purchase}/payments', [PurchaseController::class, 'storePayment'])->name('purchases.payments.store');
    Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');

    Route::patch('/staff/{staffMember}/toggle-active', [StaffMemberController::class, 'toggleActive'])->name('staff.toggle-active');
    Route::resource('staff', StaffMemberController::class)->except(['show', 'destroy'])->parameters(['staff' => 'staffMember']);

    Route::resource('expenses', ExpenseController::class)->except(['show']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
