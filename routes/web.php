<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignSendController;
use App\Http\Controllers\TrackingController;
use App\Models\Campaign;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Email tracking — PUBLIC routes hit from mail clients. Pixel is token-only
// (Gmail proxy strips signed query strings). Click and unsubscribe are signed.
Route::get('/t/o/{recipient}/{token}', [TrackingController::class, 'open'])
    ->name('tracking.open');

Route::get('/t/c/{recipient}/{token}', [TrackingController::class, 'click'])
    ->middleware('signed')
    ->name('tracking.click');

Route::get('/t/u/{recipient}/{token}', [TrackingController::class, 'unsubscribe'])
    ->middleware('signed')
    ->name('tracking.unsubscribe');

Route::post('/t/u/{recipient}/{token}', [TrackingController::class, 'unsubscribeConfirm'])
    ->middleware('signed')
    ->name('tracking.unsubscribe.confirm');

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

    Route::post('campaigns/{campaign}/send', [CampaignSendController::class, 'send'])
        ->name('campaigns.send');

    Route::get('campaigns/{campaign}/stats', [CampaignSendController::class, 'stats'])
        ->name('campaigns.stats');

    Route::post('campaigns/{campaign}/recipients/{recipient}/toggle-conversion', [CampaignSendController::class, 'toggleConversion'])
        ->name('campaigns.recipients.toggle-conversion');

    Route::post('campaigns/{campaign}/stop-followups', [CampaignSendController::class, 'stopFollowups'])
        ->name('campaigns.stop-followups');

    Route::get('campaigns/{campaign}/status', function (Campaign $campaign) {
        abort_unless($campaign->user_id === auth()->id(), 403);

        return response()->json([
            'status' => $campaign->status,
            'quality_score' => $campaign->quality_score,
            'has_analysis' => $campaign->analysis !== null,
            'has_strategy' => $campaign->strategy !== null,
            'has_creative' => $campaign->creative !== null,
            'has_audit' => $campaign->audit !== null,
            'analysis_preview' => $campaign->analysis ? [
                'segments_count' => is_array($campaign->analysis['segments'] ?? null) ? count($campaign->analysis['segments']) : 0,
                'focus_segment' => $campaign->analysis['recommended_focus_segment'] ?? null,
            ] : null,
            'strategy_preview' => $campaign->strategy ? [
                'hotel_name' => $campaign->strategy['recommended_hotel']['name'] ?? null,
                'channel' => $campaign->strategy['channel'] ?? null,
                'segment' => $campaign->strategy['target_segment']['name'] ?? null,
            ] : null,
            'creative_preview' => $campaign->creative ? [
                'subject' => $campaign->creative['subject_line'] ?? null,
            ] : null,
            'audit_preview' => $campaign->audit ? [
                'verdict' => $campaign->audit['final_verdict'] ?? null,
            ] : null,
        ]);
    })->name('campaigns.status');
});
