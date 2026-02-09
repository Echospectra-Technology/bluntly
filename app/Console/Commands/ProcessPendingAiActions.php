<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAiAction;
use App\Models\AiAction;
use Illuminate\Console\Command;

class ProcessPendingAiActions extends Command
{
    protected $signature = 'ai:process-actions';
    protected $description = 'Process pending AI actions and dispatch jobs';

    public function handle()
    {
        $this->info('Processing pending AI actions...');

        // Get actions that are scheduled and due
        $pendingActions = AiAction::pending()->get();

        if ($pendingActions->isEmpty()) {
            $this->info('No pending actions to process.');
            return 0;
        }

        $processed = 0;

        foreach ($pendingActions as $action) {
            // Dispatch job to process this action
            ProcessAiAction::dispatch($action);
            $processed++;
        }

        $this->info("Dispatched {$processed} actions to job queue.");
        return 0;
    }
}
