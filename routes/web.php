<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandSettingsController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignSendController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImageBankController;
use App\Http\Controllers\SequenceController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WebhookSettingsController;
use App\Models\Campaign;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public API documentation — no auth.
Route::view('/docs', 'docs.index')->name('docs');

// Email tracking — PUBLIC routes hit from mail clients. Security comes from
// the per-recipient 40-char `tracking_token` (240 bits of entropy). We
// deliberately don't use Laravel's `signed` middleware because HTML entity
// encoding of `&` in href attributes corrupts the `&signature=` query param
// when the URL is copy-pasted from a log or rendered by a permissive mail
// client, leading to "Invalid signature" errors. Instead, every tracked URL
// carries the token as a path segment and the click target base64-encoded
// as another path segment, so no query string = no `&amp;` bug.
Route::get('/t/o/{recipient}/{token}', [TrackingController::class, 'open'])
    ->name('tracking.open');

Route::get('/t/c/{recipient}/{token}/{target}', [TrackingController::class, 'click'])
    ->where('target', '[A-Za-z0-9\-_]+')
    ->name('tracking.click');

Route::get('/t/u/{recipient}/{token}', [TrackingController::class, 'unsubscribe'])
    ->name('tracking.unsubscribe');

Route::post('/t/u/{recipient}/{token}', [TrackingController::class, 'unsubscribeConfirm'])
    ->name('tracking.unsubscribe.confirm');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('dashboard/send-history', [DashboardController::class, 'sendHistory'])->name('dashboard.send-history');
    Route::get('dashboard/email-previews', [DashboardController::class, 'emailPreviews'])->name('dashboard.email-previews');

    // Agents
    Route::get('dashboard/agents', [AgentController::class, 'index'])->name('dashboard.agents');
    Route::post('dashboard/agents', [AgentController::class, 'store'])->name('dashboard.agents.store');
    Route::post('dashboard/agents/{customAgent}', [AgentController::class, 'update'])->name('dashboard.agents.update');
    Route::post('dashboard/agents/{customAgent}/delete', [AgentController::class, 'destroy'])->name('dashboard.agents.destroy');

    // Sequences
    Route::get('dashboard/sequences', [SequenceController::class, 'index'])->name('dashboard.sequences');
    Route::post('dashboard/sequences', [SequenceController::class, 'store'])->name('dashboard.sequences.store');
    Route::post('dashboard/sequences/{pipelineSequence}', [SequenceController::class, 'update'])->name('dashboard.sequences.update');
    Route::post('dashboard/sequences/{pipelineSequence}/delete', [SequenceController::class, 'destroy'])->name('dashboard.sequences.destroy');

    // Brand settings
    Route::get('settings/brand', [BrandSettingsController::class, 'show'])->name('settings.brand.show');
    Route::post('settings/brand', [BrandSettingsController::class, 'update'])->name('settings.brand.update');

    // Image bank
    Route::get('settings/image-bank', [ImageBankController::class, 'index'])->name('settings.image-bank.index');
    Route::post('settings/image-bank', [ImageBankController::class, 'store'])->name('settings.image-bank.store');
    Route::post('settings/image-bank/{bankImage}/delete', [ImageBankController::class, 'destroy'])->name('settings.image-bank.destroy');

    // API token management UI
    Route::get('settings/api-token', [ApiTokenController::class, 'show'])
        ->name('settings.api-token.show');
    Route::post('settings/api-token', [ApiTokenController::class, 'generate'])
        ->name('settings.api-token.generate');
    Route::post('settings/api-token/revoke', [ApiTokenController::class, 'revoke'])
        ->name('settings.api-token.revoke');

    // Webhooks live on the same page as the API token — all actions POST
    // back there and the unified settings view renders the webhook list,
    // create form and delivery history inline.
    Route::post('settings/webhooks', [WebhookSettingsController::class, 'store'])
        ->name('settings.webhooks.store');
    Route::post('settings/webhooks/{webhook}', [WebhookSettingsController::class, 'update'])
        ->name('settings.webhooks.update');
    Route::post('settings/webhooks/{webhook}/delete', [WebhookSettingsController::class, 'destroy'])
        ->name('settings.webhooks.destroy');
    Route::post('settings/webhooks/{webhook}/rotate-secret', [WebhookSettingsController::class, 'rotateSecret'])
        ->name('settings.webhooks.rotate-secret');
    Route::post('settings/webhooks/{webhook}/test', [WebhookSettingsController::class, 'sendTest'])
        ->name('settings.webhooks.test');

    Route::resource('campaigns', CampaignController::class)->only([
        'index', 'create', 'store', 'show',
    ]);

    Route::post('campaigns/{campaign}/refine-creative', [CampaignController::class, 'refineCreative'])
        ->name('campaigns.refine-creative');

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
