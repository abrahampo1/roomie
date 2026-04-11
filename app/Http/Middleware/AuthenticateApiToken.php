<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Reject the request with a 401 JSON envelope if the `Authorization:
     * Bearer {token}` header is missing or no active user matches the
     * SHA-256 hash of the presented token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->bearerToken();

        if ($header === '') {
            return $this->unauthenticated('Missing Authorization header.');
        }

        $user = User::findByApiToken($header);

        if ($user === null) {
            return $this->unauthenticated('Invalid API token.');
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }

    private function unauthenticated(string $message): JsonResponse
    {
        return response()->json([
            'error' => 'unauthenticated',
            'message' => $message,
        ], 401);
    }
}
