<?php

namespace App\Http\Controllers;

use App\Models\AiAction;
use App\Models\AiPersona;
use App\Models\WeeklyTheme;
use Illuminate\Http\Request;

class AiActionController extends Controller
{
    /**
     * Manually trigger AI post generation
     * This bypasses post frequency checks and generates a post immediately
     */
    public function generatePost(Request $request)
    {
        // Get all active personas
        $activePersonas = AiPersona::active()->get();

        if ($activePersonas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active AI personas found. Please create and activate at least one persona.',
            ], 404);
        }

        // Try to find a persona without a pending post first
        $personasWithoutPending = $activePersonas->filter(function ($persona) {
            return !AiAction::where('ai_persona_id', $persona->id)
                ->where('action_type', 'post')
                ->where('status', 'scheduled')
                ->exists();
        });

        // Select a persona without pending post, or just pick any if all have pending
        $persona = $personasWithoutPending->isNotEmpty()
            ? $personasWithoutPending->random()
            : $activePersonas->random();

        // Create the action to be processed (immediate in local, slight delay in production)
        $delay = config('app.env') === 'local' ? 0 : rand(0, 2);

        $action = AiAction::create([
            'ai_persona_id' => $persona->id,
            'action_type' => 'post',
            'target_type' => null,
            'target_id' => null,
            'status' => 'scheduled',
            'scheduled_at' => now()->addMinutes($delay),
        ]);

        // Dispatch the job to process immediately
        \App\Jobs\ProcessAiAction::dispatch($action);

        return response()->json([
            'success' => true,
            'message' => "Post generation triggered for {$persona->name}!",
            'persona' => $persona->name,
        ]);
    }
}
