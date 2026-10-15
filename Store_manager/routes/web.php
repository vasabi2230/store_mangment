<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SaleController;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/



// Custom Purchase Routes (Must be declared BEFORE Route::resource)
Route::post('purchases/bulk-delete', [PurchaseController::class, 'bulkDelete'])->name('purchases.bulkDelete');
Route::get('purchases/export', [PurchaseController::class, 'export'])->name('purchases.export');
Route::post('purchases/import', [PurchaseController::class, 'import'])->name('purchases.import');

// Custom Warehouse Routes (Must be declared BEFORE Route::resource)
Route::post('warehouses/bulk-delete', [WarehouseController::class, 'bulkDelete'])->name('warehouses.bulkDelete');
Route::get('warehouses/export', [WarehouseController::class, 'export'])->name('warehouses.export');
Route::post('warehouses/import', [WarehouseController::class, 'import'])->name('warehouses.import');

// Custom Product Routes (MUST be placed BEFORE Route::resource)
Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulkDelete');

Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');
Route::delete('/customers/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');

Route::get('/branches/export', [BranchController::class, 'export'])->name('branches.export');
Route::post('/branches/import', [BranchController::class, 'import'])->name('branches.import');
Route::post('/branches/bulk-delete', [BranchController::class, 'bulkDelete'])->name('branches.bulkDelete');

Route::get('/categories/export', [CategoryController::class, 'export'])->name('categories.export');
Route::post('/categories/import', [CategoryController::class, 'import'])->name('categories.import');
Route::post('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulkDelete');

Route::get('/sales/export', [SaleController::class, 'export'])->name('sales.export');

// Resource Management (Only list each resource ONCE at the end)
Route::resource('suppliers', SupplierController::class);
Route::resource('purchases', PurchaseController::class);
Route::resource('categories', CategoryController::class);   
Route::resource('products', ProductController::class);
Route::resource('customers', CustomerController::class);
Route::resource('units', UnitController::class);
Route::resource('branches', BranchController::class);
Route::resource('warehouses', WarehouseController::class);
Route::resource('sales', SaleController::class);