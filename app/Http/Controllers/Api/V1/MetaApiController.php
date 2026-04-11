<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LLM\LlmClientFactory;
use Illuminate\Http\JsonResponse;

class MetaApiController extends Controller
{
    /**
     * Public health endpoint, no authentication required. Handy for load
     * balancers and for the docs page's "try it" button.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'version' => 'v1',
            'service' => 'roomie-api',
        ]);
    }

    /**
     * List the LLM providers Roomie knows how to call. Used by the API docs
     * and by any integration that needs to validate its own provider selector
     * against our canonical list.
     */
    public function providers(): JsonResponse
    {
        $providers = [];
        foreach (LlmClientFactory::PROVIDERS as $id) {
            $providers[] = [
                'id' => $id,
                'label' => LlmClientFactory::label($id),
                'requires_custom_fields' => $id === 'custom',
            ];
        }

        return response()->json([
            'providers' => $providers,
        ]);
    }
}
