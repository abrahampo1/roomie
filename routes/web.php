<?php

use App\Http\Controllers\CampaignController;
use App\Models\Campaign;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::resource('campaigns', CampaignController::class)->only([
    'index', 'create', 'store', 'show',
]);

Route::get('campaigns/{campaign}/status', function (Campaign $campaign) {
    return response()->json([
        'status' => $campaign->status,
        'quality_score' => $campaign->quality_score,
        'has_analysis' => $campaign->analysis !== null,
        'has_strategy' => $campaign->strategy !== null,
        'has_creative' => $campaign->creative !== null,
        'has_audit' => $campaign->audit !== null,
    ]);
})->name('campaigns.status');
