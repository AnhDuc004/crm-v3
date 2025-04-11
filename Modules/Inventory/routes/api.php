<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Inventory\App\Http\Controllers\UnitController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:api'])->name('api.')->group(function () {
    // Api Units
    Route::get('unit', 'UnitController@index');
    Route::get('unit/{id}', 'UnitController@show');
    Route::post('unit', 'UnitController@store');
    Route::put('unit/{id}', 'UnitController@update');
    Route::delete('unit/{id}', 'UnitController@destroy');

    // Api Supplier
    Route::get('suppliers', 'SupplierController@index');
    Route::get('suppliers/{id}', 'SupplierController@show');
    Route::post('suppliers', 'SupplierController@store');
    Route::put('suppliers/{id}', 'SupplierController@update');
    Route::delete('suppliers/{id}', 'SupplierController@destroy');

    // Api Material
    Route::get('materials/list-select', 'MaterialController@listSelect');
    Route::get('materials', 'MaterialController@index');
    Route::get('materials/{id}', 'MaterialController@show');
    Route::post('materials', 'MaterialController@store');
    Route::put('materials/{id}', 'MaterialController@update');
    Route::delete('materials/{id}', 'MaterialController@destroy');

    // Api Product
    Route::get('products/list-select', 'ProductController@listSelect');
    Route::get('products', 'ProductController@index');
    Route::get('products/{id}', 'ProductController@show');
    Route::post('products', 'ProductController@store');
    Route::put('products/{id}', 'ProductController@update');
    Route::delete('products/{id}', 'ProductController@destroy');

    // Api ProductionNorm
    Route::get('production-norms', 'ProductionNormController@index');
    Route::get('production-norms/{id}', 'ProductionNormController@show');
    Route::post('production-norms', 'ProductionNormController@store');
    Route::put('production-norms/{id}', 'ProductionNormController@update');
    Route::delete('production-norms/{id}', 'ProductionNormController@destroy');

    // Api Warehouse
    Route::get('warehouses', 'WarehouseController@index');
    Route::get('warehouses/{id}', 'WarehouseController@show');
    Route::post('warehouses', 'WarehouseController@store');
    Route::put('warehouses/{id}', 'WarehouseController@update');
    Route::delete('warehouses/{id}', 'WarehouseController@destroy');

    // Api InventoryTransaction 
    Route::get('inventory-transactions', 'InventoryTransactionController@index');
    Route::get('inventory-transactions/{id}', 'InventoryTransactionController@show');
    Route::post('inventory-transactions', 'InventoryTransactionController@store');
    Route::put('inventory-transactions/{id}', 'InventoryTransactionController@update');
    Route::delete('inventory-transactions/{id}', 'InventoryTransactionController@destroy');

    // Api Stock-reports
    Route::get('stock-reports/inventory-totals', 'StockReportController@getInventoryTotals');
    Route::get('stock-reports', 'StockReportController@index');
    Route::get('stock-reports/{id}', 'StockReportController@show');
    Route::post('stock-reports', 'StockReportController@store');
    Route::put('stock-reports/{id}', 'StockReportController@update');
    Route::delete('stock-reports/{id}', 'StockReportController@destroy');


    // Api Inventory-check-reports
    Route::get('inventory-check-reports', 'InventoryCheckReportController@index');
    Route::get('inventory-check-reports/{id}', 'InventoryCheckReportController@show');
    Route::post('inventory-check-reports', 'InventoryCheckReportController@store');
    Route::put('inventory-check-reports/{id}', 'InventoryCheckReportController@update');
    Route::delete('inventory-check-reports/{id}', 'InventoryCheckReportController@destroy');

    // Api sales-orders
    Route::get('sales-orders', 'SalesOrderController@index');
    Route::get('sales-orders/{id}', 'SalesOrderController@show');
    Route::post('sales-orders', 'SalesOrderController@store');
    Route::put('sales-orders/{id}', 'SalesOrderController@update');
    Route::delete('sales-orders/{id}', 'SalesOrderController@destroy');

    // Api sales-orders-items
    Route::get('sales-order-items', 'SalesOrderItemController@index');
    Route::get('sales-order-items/{id}', 'SalesOrderItemController@show');
    Route::post('sales-order-items', 'SalesOrderItemController@store');
    Route::put('sales-order-items/{id}', 'SalesOrderItemController@update');
    Route::delete('sales-order-items/{id}', 'SalesOrderItemController@destroy');
});
