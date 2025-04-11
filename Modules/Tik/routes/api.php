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
// Api Tik Attribute
Route::get('tik-attribute', 'TikAttributeController@index');
Route::get('tik-attribute/{id}', 'TikAttributeController@show');
Route::post('tik-attribute', 'TikAttributeController@store');
Route::put('tik-attribute/{id}', 'TikAttributeController@update');
Route::delete('tik-attribute/{id}', 'TikAttributeController@destroy');

// Api Tik AttributeValue
Route::get('tik-attribute-value', 'TikAttributeValueController@index');
Route::get('tik-attribute-value/{id}', 'TikAttributeValueController@show');
Route::post('tik-attribute-value', 'TikAttributeValueController@store');
Route::put('tik-attribute-value/{id}', 'TikAttributeValueController@update');
Route::delete('tik-attribute-value/{id}', 'TikAttributeValueController@destroy');

// Api Tik Brand
Route::get('tik-brand', 'TikBrandController@index');
Route::get('tik-brand/{id}', 'TikBrandController@show');
Route::post('tik-brand', 'TikBrandController@store');
Route::put('tik-brand/{id}', 'TikBrandController@update');
Route::delete('tik-brand/{id}', 'TikBrandController@destroy');

// Api Tik Category
Route::get('tik-category', 'TikCategoryController@index');
Route::get('tik-category/{id}', 'TikCategoryController@show');
Route::post('tik-category', 'TikCategoryController@store');
Route::put('tik-category/{id}', 'TikCategoryController@update');
Route::delete('tik-category/{id}', 'TikCategoryController@destroy');

// Api Tik File
Route::get('tik-files', 'TikFileController@index');
Route::get('tik-files/{id}', 'TikFileController@show');
Route::post('tik-files', 'TikFileController@store');
Route::put('tik-files/{id}', 'TikFileController@update');
Route::delete('tik-files/{id}', 'TikFileController@destroy');

// Api Tik Sku
Route::get('tik-sku', 'TikSkuController@index');
Route::get('tik-sku/{id}', 'TikSkuController@show');
Route::post('tik-sku', 'TikSkuController@store');
Route::put('tik-sku/{id}', 'TikSkuController@update');
Route::delete('tik-sku/{id}', 'TikSkuController@destroy');

// Api Tik Product
Route::get('tik-product', 'TikProductController@index');
Route::get('tik-product/{id}', 'TikProductController@show');
Route::post('tik-product', 'TikProductController@store');
Route::put('tik-product/{id}', 'TikProductController@update');
Route::delete('tik-product/{id}', 'TikProductController@destroy');

// Api Tik PoductSaleAttribute
Route::get('tik-product-sales-attribute', 'TikProductSalesAttributeController@index');
Route::get('tik-product-sales-attribute/{id}', 'TikProductSalesAttributeController@show');
Route::post('tik-product-sales-attribute', 'TikProductSalesAttributeController@store');
Route::put('tik-product-sales-attribute/{id}', 'TikProductSalesAttributeController@update');
Route::delete('tik-product-sales-attribute/{id}', 'TikProductSalesAttributeController@destroy');

// Api Tik Poduct certification
Route::get('tik-product-certification', 'TikProductCertificationController@index');
Route::get('tik-product-certification/{id}', 'TikProductCertificationController@show');
Route::post('tik-product-certification', 'TikProductCertificationController@store');
Route::put('tik-product-certification/{id}', 'TikProductCertificationController@update');
Route::post('tik-product-certification/upload-files/{id}', 'TikProductCertificationController@uploadFiles');
Route::delete('tik-product-certification/{id}', 'TikProductCertificationController@destroy');

// Api Tik Poduct Image
Route::get('tik-product-image', 'TikProductImageController@index');
Route::get('tik-product-image/{id}', 'TikProductImageController@show');
Route::post('tik-product-image', 'TikProductImageController@store');
Route::put('tik-product-image/{id}', 'TikProductImageController@update');
Route::post('tik-product-image/upload-files/{id}', 'TikProductImageController@updateWithImages');
Route::delete('tik-product-image/{id}', 'TikProductImageController@destroy');
});
