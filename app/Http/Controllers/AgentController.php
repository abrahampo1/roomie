<?php

namespace App\Http\Controllers;

use App\Models\CustomAgent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(): View
    {
        $agents = auth()->user()->customAgents()->latest()->get();

        $builtinAgents = [
            ['name' => 'Analista', 'role' => 'analyst', 'description' => 'Segmenta la base de clientes y analiza el mercado para identificar oportunidades.', 'icon' => 'chart'],
            ['name' => 'Estratega', 'role' => 'strategist', 'description' => 'Define la estrategia de campaña: hotel, canal, timing, segmento objetivo y mensaje clave.', 'icon' => 'target'],
            ['name' => 'Creativo', 'role' => 'creative', 'description' => 'Genera el email editorial completo con subject, headline, body HTML y CTA.', 'icon' => 'pen'],
            ['name' => 'Auditor', 'role' => 'auditor', 'description' => 'Evalúa coherencia, calidad y da un quality score de 0-100.', 'icon' => 'shield'],
        ];

        return view('dashboard.agents', compact('agents', 'builtinAgents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:60'],
            'system_prompt' => ['required', 'string', 'max:10000'],
            'output_schema' => ['nullable', 'string', 'max:5000'],
            'icon' => ['nullable', 'string', 'max:30'],
        ]);

        auth()->user()->customAgents()->create($validated);

        return back()->with('message', 'Agente creado.');
    }

    public function update(Request $request, CustomAgent $customAgent): RedirectResponse
    {
        abort_unless($customAgent->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:60'],
            'system_prompt' => ['required', 'string', 'max:10000'],
            'output_schema' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customAgent->update($validated);

        return back()->with('message', 'Agente actualizado.');
    }

    public function destroy(CustomAgent $customAgent): RedirectResponse
    {
        abort_unless($customAgent->user_id === auth()->id(), 403);

        $customAgent->delete();

        return back()->with('message', 'Agente eliminado.');
    }
}
