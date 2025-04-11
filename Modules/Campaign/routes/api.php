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
    // Route::get('campaign', fn (Request $request) => $request->user())->name('campaign');
    //api campaign
    Route::apiResource('campaign', 'CampaignController');

    //api campaign-content
    Route::apiResource('campaign-content', 'CampaignContentController');

    //api campaign-exe
    Route::apiResource('campaign-exe', 'CampaignExeController');

    //api campaign-group
    Route::apiResource('campaign-group', 'CampaignGroupController');

    //api campaign-image
    Route::apiResource('campaign-image', 'CampaignImageController');

    //api domain
    Route::apiResource('domain', 'DomainController');

    //api fbGroup
    Route::apiResource('fbGroup', 'FbGroupController');

    //api group-domain
    Route::apiResource('group-domain', 'GroupDomainController');
});
