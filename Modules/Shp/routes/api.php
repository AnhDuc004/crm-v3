<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
// Api Shp brand
Route::get('shp-brand', 'ShpBrandController@index');
Route::get('shp-brand/{id}', 'ShpBrandController@show');
Route::post('shp-brand', 'ShpBrandController@store');
Route::put('shp-brand/{id}', 'ShpBrandController@update');
Route::delete('shp-brand/{id}', 'ShpBrandController@destroy');

// Api Shp Product
Route::get('shp-product', 'ShpProductController@index');
Route::get('shp-product/{id}', 'ShpProductController@show');
Route::post('shp-product', 'ShpProductController@store');
Route::put('shp-product/{id}', 'ShpProductController@update');
Route::delete('shp-product/{id}', 'ShpProductController@destroy');

// Api Shp Category
Route::get('shp-category', 'ShpCategoryController@index');
Route::get('shp-category/{id}', 'ShpCategoryController@show');
Route::post('shp-category', 'ShpCategoryController@store');
Route::put('shp-category/{id}', 'ShpCategoryController@update');
Route::delete('shp-category/{id}', 'ShpCategoryController@destroy');

// Api Shp Attribute
Route::get('shp-attribute', 'ShpAttributeController@index');
Route::get('shp-attribute/{id}', 'ShpAttributeController@show');
Route::post('shp-attribute', 'ShpAttributeController@store');
Route::put('shp-attribute/{id}', 'ShpAttributeController@update');
Route::delete('shp-attribute/{id}', 'ShpAttributeController@destroy');

// Api Shp Dimension
Route::get('shp-dimension', 'ShpDimensionController@index');
Route::get('shp-dimension/{id}', 'ShpDimensionController@show');
Route::post('shp-dimension', 'ShpDimensionController@store');
Route::put('shp-dimension/{id}', 'ShpDimensionController@update');
Route::delete('shp-dimension/{id}', 'ShpDimensionController@destroy');

// Api Shp Gtin
Route::get('shp-gtin', 'ShpGtinController@index');
Route::get('shp-gtin/{id}', 'ShpGtinController@show');
Route::post('shp-gtin', 'ShpGtinController@store');
Route::put('shp-gtin/{id}', 'ShpGtinController@update');
Route::delete('shp-gtin/{id}', 'ShpGtinController@destroy');

// Api Shp Image
Route::get('shp-image', 'ShpImageController@index');
Route::get('shp-image/{id}', 'ShpImageController@show');
Route::post('shp-image', 'ShpImageController@store');
Route::put('shp-image/{id}', 'ShpImageController@update');
Route::delete('shp-image/{id}', 'ShpImageController@destroy');

// Api Shp Logistic
Route::get('shp-logistic', 'ShpLogisticController@index');
Route::get('shp-logistic/{id}', 'ShpLogisticController@show');
Route::post('shp-logistic', 'ShpLogisticController@store');
Route::put('shp-logistic/{id}', 'ShpLogisticController@update');
Route::delete('shp-logistic/{id}', 'ShpLogisticController@destroy');

// Api Shp Preorder
Route::get('shp-preorder', 'ShpPreorderController@index');
Route::get('shp-preorder/{id}', 'ShpPreorderController@show');
Route::post('shp-preorder', 'ShpPreorderController@store');
Route::put('shp-preorder/{id}', 'ShpPreorderController@update');
Route::delete('shp-preorder/{id}', 'ShpPreorderController@destroy');

// Api Shp Seller Stocks
Route::get('shp-seller-stocks', 'ShpSellerStockController@index');
Route::get('shp-seller-stocks/{id}', 'ShpSellerStockController@show');
Route::post('shp-seller-stocks', 'ShpSellerStockController@store');
Route::put('shp-seller-stocks/{id}', 'ShpSellerStockController@update');
Route::delete('shp-seller-stocks/{id}', 'ShpSellerStockController@destroy');

// Api Shp Tax Info
Route::get('shp-tax-info', 'ShpTaxInfoController@index');
Route::get('shp-tax-info/{id}', 'ShpTaxInfoController@show');
Route::post('shp-tax-info', 'ShpTaxInfoController@store');
Route::put('shp-tax-info/{id}', 'ShpTaxInfoController@update');
Route::delete('shp-tax-info/{id}', 'ShpTaxInfoController@destroy');

// Api Shp Video
Route::get('shp-video', 'ShpVideoController@index');
Route::get('shp-video/{id}', 'ShpVideoController@show');
Route::post('shp-video', 'ShpVideoController@store');
Route::put('shp-video/{id}', 'ShpVideoController@update');
Route::delete('shp-video/{id}', 'ShpVideoController@destroy');

// Api Shp Wholesale
Route::get('shp-wholesale', 'ShpWholesaleController@index');
Route::get('shp-wholesale/{id}', 'ShpWholesaleController@show');
Route::post('shp-wholesale', 'ShpWholesaleController@store');
Route::put('shp-wholesale/{id}', 'ShpWholesaleController@update');
Route::delete('shp-wholesale/{id}', 'ShpWholesaleController@destroy');
});
