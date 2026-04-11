<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Models\Campaign;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('campaigns', CampaignController::class)->only([
        'index', 'create', 'store', 'show',
    ]);

    Route::get('campaigns/{campaign}/status', function (Campaign $campaign) {
        abort_unless($campaign->user_id === auth()->id(), 403);

        return response()->json([
            'status' => $campaign->status,
            'quality_score' => $campaign->quality_score,
            'has_analysis' => $campaign->analysis !== null,
            'has_strategy' => $campaign->strategy !== null,
            'has_creative' => $campaign->creative !== null,
            'has_audit' => $campaign->audit !== null,
        ]);
    })->name('campaigns.status');
});
