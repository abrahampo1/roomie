<?php

namespace App\Http\Controllers;

use App\Models\PipelineSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SequenceController extends Controller
{
    public function index(): View
    {
        $sequences = auth()->user()->pipelineSequences()->latest()->get();
        $agents = auth()->user()->customAgents()->where('is_active', true)->get();

        $builtinSteps = [
            ['role' => 'analyst', 'label' => 'Analista'],
            ['role' => 'strategist', 'label' => 'Estratega'],
            ['role' => 'creative', 'label' => 'Creativo'],
            ['role' => 'auditor', 'label' => 'Auditor'],
        ];

        return view('dashboard.sequences', compact('sequences', 'agents', 'builtinSteps'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'steps' => ['required', 'array', 'min:1', 'max:10'],
            'steps.*.role' => ['required', 'string', 'max:60'],
            'steps.*.agent_id' => ['nullable', 'integer'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            auth()->user()->pipelineSequences()->update(['is_default' => false]);
        }

        auth()->user()->pipelineSequences()->create($validated);

        return back()->with('message', 'Secuencia creada.');
    }

    public function update(Request $request, PipelineSequence $pipelineSequence): RedirectResponse
    {
        abort_unless($pipelineSequence->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'steps' => ['required', 'array', 'min:1', 'max:10'],
            'steps.*.role' => ['required', 'string', 'max:60'],
            'steps.*.agent_id' => ['nullable', 'integer'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            auth()->user()->pipelineSequences()->where('id', '!=', $pipelineSequence->id)->update(['is_default' => false]);
        }

        $pipelineSequence->update($validated);

        return back()->with('message', 'Secuencia actualizada.');
    }

    public function destroy(PipelineSequence $pipelineSequence): RedirectResponse
    {
        abort_unless($pipelineSequence->user_id === auth()->id(), 403);

        $pipelineSequence->delete();

        return back()->with('message', 'Secuencia eliminada.');
    }
}
