<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScheduleAiActions extends Command
{
    protected $signature   = 'ai:schedule-actions';
    protected $description = 'Start the AI scheduling job (runs automatically every 15 minutes)';

    public function handle()
    {
        $this->info('Dispatching ScheduleAiActions job...');

        \App\Jobs\ScheduleAiActions::dispatch();

        $this->info('Job dispatched successfully. It will run every 15 minutes automatically.');
        return 0;
    }
}
