<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function show(): View
    {
        return view('settings.api-token', [
            'user' => Auth::user(),
            // $newToken is flashed to the session exactly once when a token
            // is generated. After the first render it disappears forever.
            'newToken' => session('new_token'),
        ]);
    }

    public function generate(): RedirectResponse
    {
        $plain = Auth::user()->generateApiToken();

        return redirect()
            ->route('settings.api-token.show')
            ->with('new_token', $plain);
    }

    public function revoke(): RedirectResponse
    {
        Auth::user()->revokeApiToken();

        return redirect()
            ->route('settings.api-token.show')
            ->with('message', 'Token revocado. Cualquier integración que lo estuviera usando ya no tiene acceso.');
    }
}
