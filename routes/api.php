<?php

use App\Http\Controllers\Api\V1\CampaignApiController;
use App\Http\Controllers\Api\V1\MetaApiController;
use App\Http\Controllers\Api\V1\WebhookApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
|
| All routes here are automatically prefixed with `/api` and wrapped in the
| stateless `api` middleware group (no session, no CSRF). Authentication is
| handled by the custom `api.token` alias registered in bootstrap/app.php —
| it reads `Authorization: Bearer {token}` and resolves it to a user via
| the SHA-256 hash stored on users.api_token.
|
| Public endpoints live outside the `api.token` group. Protected endpoints
| are rate-limited with Laravel's built-in throttle middleware.
|
*/

Route::prefix('v1')->group(function () {
    // Public — no auth
    Route::get('health', [MetaApiController::class, 'health'])->name('api.v1.health');

    Route::middleware(['api.token'])->group(function () {
        Route::get('providers', [MetaApiController::class, 'providers'])
            ->middleware('throttle:60,1')
            ->name('api.v1.providers');

        // Read-heavy campaign endpoints — generous throttle
        Route::middleware('throttle:60,1')->group(function () {
            Route::get('campaigns', [CampaignApiController::class, 'index'])
                ->name('api.v1.campaigns.index');
            Route::get('campaigns/{campaign}', [CampaignApiController::class, 'show'])
                ->name('api.v1.campaigns.show');
            Route::get('campaigns/{campaign}/status', [CampaignApiController::class, 'status'])
                ->name('api.v1.campaigns.status');
            Route::get('campaigns/{campaign}/stats', [CampaignApiController::class, 'stats'])
                ->name('api.v1.campaigns.stats');
            Route::get('campaigns/{campaign}/recipients', [CampaignApiController::class, 'recipients'])
                ->name('api.v1.campaigns.recipients');

            Route::get('webhooks', [WebhookApiController::class, 'index'])
                ->name('api.v1.webhooks.index');
            Route::get('webhooks/{webhook}', [WebhookApiController::class, 'show'])
                ->name('api.v1.webhooks.show');
            Route::get('webhooks/{webhook}/deliveries', [WebhookApiController::class, 'deliveries'])
                ->name('api.v1.webhooks.deliveries');
        });

        // Write-heavy endpoints — stricter throttle
        Route::middleware('throttle:20,1')->group(function () {
            Route::post('campaigns', [CampaignApiController::class, 'store'])
                ->name('api.v1.campaigns.store');
            Route::post('campaigns/{campaign}/refine-creative', [CampaignApiController::class, 'refineCreative'])
                ->name('api.v1.campaigns.refine-creative');
            Route::post('campaigns/{campaign}/send', [CampaignApiController::class, 'send'])
                ->name('api.v1.campaigns.send');
            Route::post('campaigns/{campaign}/stop-followups', [CampaignApiController::class, 'stopFollowups'])
                ->name('api.v1.campaigns.stop-followups');
            Route::post('campaigns/{campaign}/recipients/{recipient}/toggle-conversion', [CampaignApiController::class, 'toggleConversion'])
                ->name('api.v1.campaigns.toggle-conversion');

            Route::post('webhooks', [WebhookApiController::class, 'store'])
                ->name('api.v1.webhooks.store');
            Route::patch('webhooks/{webhook}', [WebhookApiController::class, 'update'])
                ->name('api.v1.webhooks.update');
            Route::post('webhooks/{webhook}/rotate-secret', [WebhookApiController::class, 'rotateSecret'])
                ->name('api.v1.webhooks.rotate-secret');
            Route::post('webhooks/{webhook}/test', [WebhookApiController::class, 'sendTest'])
                ->name('api.v1.webhooks.test');
            Route::delete('webhooks/{webhook}', [WebhookApiController::class, 'destroy'])
                ->name('api.v1.webhooks.destroy');
        });
    });
});
